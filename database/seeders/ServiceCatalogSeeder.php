<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

// Keyed by slug and create-only, so re-running it keeps an admin's later edits.
class ServiceCatalogSeeder extends Seeder
{
    private const IRPF_DOCUMENTS = [
        'Documento de identificação com CPF',
        'Comprovante de residência atualizado',
        'Recibo e cópia da última declaração entregue',
        'Informes de rendimentos (empregador, bancos, INSS)',
        'Comprovantes de despesas médicas e odontológicas',
        'Comprovantes de despesas com educação',
        'Dados bancários para restituição',
    ];

    private const CATALOG = [
        'Fiscal' => [
            'Declaração de imposto de renda' => self::IRPF_DOCUMENTS,
            'IRPF' => self::IRPF_DOCUMENTS,
            'Atendimento MEI' => [
                'CNPJ ou Certificado da Condição de MEI (CCMEI)',
                'Login e senha da conta gov.br (nível prata ou ouro)',
                'Relatório mensal de receitas brutas',
                'Comprovantes das guias DAS pagas',
            ],
            'Emissão de guias para pagamento de tributos' => [
                'CPF ou CNPJ do contribuinte',
                'Login e senha da conta gov.br',
                'Período de apuração da guia',
            ],
            'Atendimento externo' => [],
        ],
        'Finanças Pessoais' => [
            'Orçamento familiar e pessoal' => [
                'Extratos bancários dos últimos 3 meses',
                'Faturas de cartão de crédito dos últimos 3 meses',
                'Lista de receitas e despesas fixas da família',
            ],
            'Fluxo de caixa' => [
                'Extratos bancários dos últimos 3 meses',
                'Lista de entradas e saídas previstas',
            ],
            'Planejamento financeiro' => [],
        ],
        'Empresarial' => [
            'Formação do preço de venda' => [
                'Notas fiscais de compra de insumos',
                'Lista de custos fixos mensais',
                'Preços praticados atualmente',
            ],
            'Cálculo de custos' => [
                'Notas fiscais de compra de insumos',
                'Lista de custos fixos mensais',
                'Folha de pagamento ou pró-labore',
            ],
            'Orçamento empresarial' => [],
            'Mapeamento de processos' => [],
        ],
        'Terceiro Setor' => [
            'Prestação de contas para entidades do terceiro setor' => [
                'Estatuto social',
                'Ata de eleição da diretoria atual',
                'Extratos bancários do período',
                'Notas fiscais e recibos das despesas',
                'Termos de convênio ou parceria, quando houver',
            ],
            'Elaboração de demonstrações contábeis' => [],
            'Elaboração de relatório de responsabilidade social' => [],
        ],
        'Outros' => [
            'Outros' => [],
        ],
    ];

    public function run(): void
    {
        $categoryPosition = 0;

        foreach (self::CATALOG as $categoryName => $services) {
            $category = ServiceCategory::query()->firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'sort_order' => $categoryPosition++],
            );

            $servicePosition = 0;

            foreach ($services as $serviceName => $documents) {
                $service = Service::query()->firstOrCreate(
                    ['slug' => Str::slug($serviceName)],
                    [
                        'service_category_id' => $category->id,
                        'name' => $serviceName,
                        'requires_details' => $serviceName === 'Outros',
                        'sort_order' => $servicePosition++,
                    ],
                );

                if (! $service->wasRecentlyCreated) {
                    continue;
                }

                foreach ($documents as $position => $document) {
                    $service->documents()->create(['name' => $document, 'sort_order' => $position]);
                }
            }
        }
    }
}
