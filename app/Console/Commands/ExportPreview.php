<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Appointments\Queries\AgendaAppointments;
use App\Http\Resources\AgendaEventResource;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use RuntimeException;

/**
 * Renders the real pages, with the demo data, into static HTML for a
 * read-only preview (GitHub Pages). Nothing is duplicated: the same routes,
 * views and assets produce every file.
 */
final class ExportPreview extends Command
{
    protected $signature = 'preview:export
        {url : Public address of the preview, e.g. https://user.github.io/Agenda-Contabil}
        {--output=build/preview : Directory that receives the static site}';

    protected $description = 'Export a read-only static preview of the application with the demo data';

    public function handle(Kernel $kernel, Filesystem $files): int
    {
        // The preview is public: it must never be built from real people's data.
        if ($this->laravel->isProduction()) {
            $this->components->error('The preview is built from demo data only and cannot run in production.');

            return self::FAILURE;
        }

        $base = rtrim((string) $this->argument('url'), '/');
        $output = base_path((string) $this->option('output'));
        $admin = User::query()->where('email', 'admin@agenda.local')->first();

        if ($admin === null || Appointment::query()->doesntExist()) {
            $this->components->error('Seed the demo data first: php artisan migrate:fresh --seed');

            return self::FAILURE;
        }

        URL::forceRootUrl($base);
        URL::useAssetOrigin($base);

        $files->deleteDirectory($output);
        $files->ensureDirectoryExists($output);

        $login = $this->render($kernel, '/login', null, $base);
        $this->write($files, $output, '/', $login);
        $this->write($files, $output, '/login', $login);

        foreach ($this->privatePages() as $path) {
            $this->write($files, $output, $path, $this->render($kernel, $path, $admin, $base));
        }

        $files->put("{$output}/agenda/eventos.json", $this->agendaFeed($admin));
        $files->copyDirectory(public_path('build'), "{$output}/build");
        $files->copy(public_path('favicon.ico'), "{$output}/favicon.ico");
        $files->put("{$output}/.nojekyll", '');

        $this->components->info("Preview written to {$output}");

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function privatePages(): array
    {
        $clients = Client::query()->pluck('id');

        return [
            '/dashboard',
            '/agenda',
            '/atendidos',
            '/atendidos/novo',
            '/atendimentos/novo',
            '/perfil',
            ...$clients->map(fn (int $id): string => "/atendidos/{$id}"),
            ...$clients->map(fn (int $id): string => "/atendidos/{$id}/editar"),
            ...Appointment::query()->pluck('id')->map(fn (int $id): string => "/atendimentos/{$id}"),
        ];
    }

    private function render(Kernel $kernel, string $path, ?User $user, string $base): string
    {
        $user === null ? Auth::guard('web')->forgetUser() : Auth::guard('web')->setUser($user);

        $request = Request::create($path);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        if (! $response->isOk()) {
            throw new RuntimeException("{$path} answered HTTP {$response->getStatusCode()}.");
        }

        return $this->forStaticHosting((string) $response->getContent(), $base);
    }

    private function forStaticHosting(string $html, string $base): string
    {
        $html = preg_replace(
            '/<html([^>]*)>/',
            '<html$1 data-preview data-preview-dashboard="'.e($base).'/dashboard" data-preview-login="'.e($base).'/login">',
            $html,
            1,
        );

        // Static files cannot answer date-range queries or accept changes:
        // the agenda reads one prepared feed and slot selection is off.
        $html = preg_replace('/data-events-url="[^"]*"/', 'data-events-url="'.e($base).'/agenda/eventos.json"', $html);
        $html = preg_replace('/data-create-url="[^"]*"/', 'data-create-url=""', $html);

        return str_replace('</body>', view('preview.banner')->render().'</body>', $html);
    }

    private function agendaFeed(User $admin): string
    {
        $request = Request::create('/agenda/eventos');
        $request->setUserResolver(fn (): User => $admin);

        $events = AgendaEventResource::collection(AgendaAppointments::between(now()->subDays(90), now()->addDays(90)))
            ->resolve($request);

        return (string) json_encode(
            array_map(fn (array $event): array => [...$event, 'editable' => false], $events),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }

    private function write(Filesystem $files, string $output, string $path, string $html): void
    {
        $directory = rtrim($output.$path, '/');

        $files->ensureDirectoryExists($directory);
        $files->put("{$directory}/index.html", $html);
    }
}
