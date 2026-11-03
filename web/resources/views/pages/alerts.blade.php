@extends('layouts.app')

@section('content')
    <div
        x-data="{
            alerts: @js($alerts),
            get openCount() { return this.alerts.filter(alert => !alert.done).length },
            countBySeverity(severity) { return this.alerts.filter(alert => !alert.done && alert.severity === severity).length },
            markDone(id) {
                const alert = this.alerts.find(item => item.id === id);
                if (!alert || alert.done) return;
                alert.done = true;
                this.$dispatch('alerts-updated', this.openCount);
            }
        }"
        class="flex flex-col gap-5"
    >
        <section class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <h1 class="font-display text-[23px] leading-tight font-bold tracking-tight">Early Warning & Alerts</h1>
                <p class="mt-1.5 text-xs text-berry-muted">Prioritaskan tindakan lapangan berdasarkan tingkat risiko kebun.</p>
            </div>
            <span
                x-bind:class="openCount > 0 ? 'border-berry-crimson-border bg-berry-crimson-soft text-berry-crimson' : 'border-berry-green-border bg-berry-green-soft text-berry-green-dark'"
                class="inline-flex w-fit items-center gap-1.5 rounded-badge border px-1.5 py-1 text-[9px] leading-none font-bold whitespace-nowrap"
            ><span x-text="openCount"></span> peringatan belum selesai</span>
        </section>

        <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="flex items-center gap-3 rounded-nav border border-berry-border bg-white px-[18px] py-[15px] text-[10px] text-berry-muted">
                <span class="font-display text-xl font-bold text-berry-crimson" x-text="countBySeverity('Kritis')"></span><span>Kritis</span>
            </div>
            <div class="flex items-center gap-3 rounded-nav border border-berry-border bg-white px-[18px] py-[15px] text-[10px] text-berry-muted">
                <span class="font-display text-xl font-bold text-berry-amber" x-text="countBySeverity('Waspada')"></span><span>Waspada</span>
            </div>
            <div class="flex items-center gap-3 rounded-nav border border-berry-border bg-white px-[18px] py-[15px] text-[10px] text-berry-muted">
                <span class="font-display text-xl font-bold text-berry-blue" x-text="countBySeverity('Info')"></span><span>Informasi</span>
            </div>
        </section>

        <section class="flex flex-col gap-2.5">
            <template x-for="alert in alerts" :key="alert.id">
                <article
                    x-bind:class="{
                        'border-l-berry-green opacity-70': alert.done,
                        'border-l-berry-crimson': !alert.done && alert.severity === 'Kritis',
                        'border-l-amber-400': !alert.done && alert.severity === 'Waspada',
                        'border-l-berry-blue': !alert.done && alert.severity === 'Info'
                    }"
                    class="grid grid-cols-[40px_minmax(0,1fr)] items-start gap-3 rounded-card border border-berry-border border-l-[3px] bg-white p-[17px] shadow-card sm:grid-cols-[40px_minmax(0,1fr)_auto] sm:gap-[15px]"
                >
                    <span
                        x-bind:class="{
                            'bg-berry-green-soft text-berry-green': alert.done,
                            'bg-berry-crimson-soft text-berry-crimson': !alert.done && alert.severity === 'Kritis',
                            'bg-berry-amber-soft text-berry-amber': !alert.done && alert.severity === 'Waspada',
                            'bg-berry-blue-soft text-berry-blue': !alert.done && alert.severity === 'Info'
                        }"
                        class="grid size-[38px] place-items-center rounded-control"
                    >
                        <span x-show="alert.done"><x-icon name="check"/></span>
                        <span x-show="!alert.done && alert.severity !== 'Info'"><x-icon name="alert"/></span>
                        <span x-show="!alert.done && alert.severity === 'Info'"><x-icon name="bell"/></span>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span
                                x-bind:class="{
                                    'border-berry-green-border bg-berry-green-soft text-berry-green-dark': alert.done,
                                    'border-berry-crimson-border bg-berry-crimson-soft text-berry-crimson': !alert.done && alert.severity === 'Kritis',
                                    'border-berry-amber-border bg-berry-amber-soft text-berry-amber': !alert.done && alert.severity === 'Waspada',
                                    'border-berry-blue-border bg-berry-blue-soft text-berry-blue': !alert.done && alert.severity === 'Info'
                                }"
                                class="inline-flex w-fit items-center gap-1.5 rounded-badge border px-1.5 py-1 text-[9px] leading-none font-bold whitespace-nowrap"
                                x-text="alert.done ? 'Selesai' : alert.severity"
                            ></span>
                            <span class="text-[10px] text-berry-muted" x-text="alert.time"></span>
                        </div>
                        <h2 x-bind:class="{ 'line-through': alert.done }" class="mt-2 font-display text-[13px] font-bold" x-text="alert.title"></h2>
                        <p class="mt-1 text-[10px] text-berry-muted" x-text="alert.location"></p>
                        <div class="mt-3 flex flex-col gap-1 rounded-badge bg-berry-canvas px-3 py-2.5 text-[10px] text-berry-slate-700">
                            <strong class="text-[9px] uppercase tracking-wide text-berry-muted">Tindakan yang direkomendasikan</strong>
                            <span x-text="alert.action"></span>
                        </div>
                    </div>
                    <div class="col-start-2 sm:col-start-auto">
                        <button
                            type="button"
                            x-show="!alert.done"
                            @click="markDone(alert.id)"
                            class="inline-flex min-h-9 items-center justify-center gap-2 rounded-control border border-transparent bg-berry-green px-3 text-[11px] font-semibold whitespace-nowrap text-white transition hover:bg-berry-green-dark"
                        ><x-icon name="check" size="16"/>Tandai Selesai</button>
                        <button
                            type="button"
                            x-show="alert.done"
                            disabled
                            class="inline-flex min-h-9 items-center justify-center gap-2 rounded-control border border-berry-border bg-white px-3 text-[11px] font-semibold whitespace-nowrap text-berry-slate-700 opacity-65"
                        ><x-icon name="check" size="16"/>Sudah Selesai</button>
                    </div>
                </article>
            </template>
        </section>
    </div>
@endsection
