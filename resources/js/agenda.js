import { Calendar } from '@fullcalendar/core';
import ptBrLocale from '@fullcalendar/core/locales/pt-br';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';

// Wall times in and out: the calendar runs in "UTC" so the browser never
// shifts São Paulo times by its own clock.
const wallTime = (date) => date.toISOString().slice(0, 19);

const element = document.querySelector('[data-agenda]');
const message = document.querySelector('[data-agenda-message]');
const filters = {
    responsible: document.querySelector('[data-agenda-filter="responsible"]'),
    cancelled: document.querySelector('[data-agenda-filter="cancelled"]'),
};
const { eventsUrl, createUrl, rescheduleUrl, textSaving, textSaved, textFailed } = element.dataset;
const canCreate = createUrl !== '';

// One layout per screen size, first match wins. Tablets get three days so
// each column stays wide enough to read a name.
const layouts = [
    {
        query: window.matchMedia('(max-width: 639px)'),
        view: 'listWeek',
        header: { left: 'title', right: 'today prev,next' },
        footer: { center: 'listWeek,timeGridDay,dayGridMonth' },
    },
    {
        query: window.matchMedia('(max-width: 1023px)'),
        view: 'timeGridThreeDay',
        header: { left: 'prev,next today', center: 'title', right: 'timeGridDay,timeGridThreeDay,listWeek' },
        footer: false,
    },
    {
        query: window.matchMedia('all'),
        view: 'timeGridWeek',
        header: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
        footer: false,
    },
];
const currentLayout = () => layouts.find((layout) => layout.query.matches);

function announce(text, tone) {
    message.textContent = text;
    message.className = `rise-in rounded-soft px-4 py-3 text-sm ${tone === 'error' ? 'bg-danger-soft text-danger' : 'bg-moss-soft text-moss'}`;
}

async function reschedule(info) {
    announce(textSaving, 'info');

    const response = await fetch(rescheduleUrl.replace('__ID__', info.event.id), {
        method: 'PATCH',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            starts_at: wallTime(info.event.start),
            ends_at: wallTime(info.event.end),
        }),
    });

    if (response.ok) {
        announce(textSaved, 'success');

        return;
    }

    info.revert();

    const body = await response.json().catch(() => ({}));
    const firstError = Object.values(body.errors ?? {})[0]?.[0];

    announce(firstError ?? textFailed, 'error');
}

function openCreateForm(info) {
    // A click on a month cell has no time; start at 09:00 like the office.
    const start = info.allDay ? `${info.startStr}T09:00:00` : wallTime(info.start);
    const params = new URLSearchParams({ inicio: start });

    if (! info.allDay) {
        params.set('fim', wallTime(info.end));
    }

    window.location.href = `${createUrl}?${params}`;
}

function node(tag, className, text = '', children = []) {
    const element = document.createElement(tag);
    element.className = className;
    element.textContent = text;
    element.append(...children);

    return element;
}

// Each family of views has its own space to work with: a month cell fits one
// line, a time column fits a small card, the list has a whole row.
const renderers = {
    dayGrid: ({ event, timeText }) => [
        node('div', 'agenda-event__line', '', [
            node('span', 'agenda-event__time', timeText),
            node('span', 'agenda-event__title', event.title),
        ]),
    ],
    timeGrid: ({ event, timeText }) => [
        node('div', 'agenda-event__time', timeText),
        node('div', 'agenda-event__title', event.title),
        node('div', 'agenda-event__meta', event.extendedProps.service),
    ],
    list: ({ event }) => [
        node('div', 'agenda-event__row', '', [
            node('div', 'min-w-0', '', [
                node('div', 'agenda-event__title', event.title),
                node('div', 'agenda-event__meta', `${event.extendedProps.service} · ${event.extendedProps.responsible}`),
            ]),
            node('span', 'agenda-event__status', event.extendedProps.status),
        ]),
    ],
};

const viewFamily = (viewType) => viewType.replace(/(Month|Week|ThreeDay|Day)$/, '');

const renderEvent = (arg) => ({ domNodes: renderers[viewFamily(arg.view.type)](arg) });

const calendar = new Calendar(element, {
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
    locale: ptBrLocale,
    timeZone: 'UTC',
    firstDay: 1,
    initialView: currentLayout().view,
    headerToolbar: currentLayout().header,
    footerToolbar: currentLayout().footer,
    height: 'auto',
    eventTimeFormat: { hour: '2-digit', minute: '2-digit' },
    slotLabelFormat: { hour: '2-digit', minute: '2-digit' },
    listDayFormat: { weekday: 'long', day: 'numeric' },
    listDaySideFormat: false,
    slotEventOverlap: false,
    eventMinHeight: 28,
    allDaySlot: false,
    slotMinTime: '07:00:00',
    slotMaxTime: '22:00:00',
    slotDuration: '00:30:00',
    snapDuration: '00:15:00',
    nowIndicator: true,
    views: {
        // The project works Monday to Saturday; the month view keeps the
        // full week so dates line up with a paper calendar.
        timeGridWeek: { hiddenDays: [0] },
        timeGridThreeDay: { type: 'timeGrid', duration: { days: 3 }, buttonText: '3 dias' },
        dayGridMonth: { eventDisplay: 'block', dayMaxEvents: 3 },
    },
    noEventsContent: element.dataset.textEmpty,
    businessHours: { daysOfWeek: [1, 2, 3, 4, 5, 6], startTime: '08:00', endTime: '21:30' },
    selectable: canCreate,
    selectMirror: true,
    editable: false,
    eventDurationEditable: true,
    eventContent: renderEvent,
    eventDidMount: ({ event, el }) => {
        el.title = [event.title, event.extendedProps.service, event.extendedProps.responsible, event.extendedProps.status].join(' · ');
    },
    select: openCreateForm,
    eventDrop: reschedule,
    eventResize: reschedule,
    events: {
        url: eventsUrl,
        extraParams: () => ({
            responsible: filters.responsible.value,
            cancelled: filters.cancelled.checked ? 1 : 0,
        }),
    },
});

calendar.render();

let activeLayout = currentLayout();

window.addEventListener('resize', () => {
    const layout = currentLayout();

    if (layout === activeLayout) {
        return;
    }

    activeLayout = layout;
    calendar.setOption('headerToolbar', layout.header);
    calendar.setOption('footerToolbar', layout.footer);
    calendar.changeView(layout.view);
});

Object.values(filters).forEach((filter) => filter.addEventListener('change', () => calendar.refetchEvents()));
