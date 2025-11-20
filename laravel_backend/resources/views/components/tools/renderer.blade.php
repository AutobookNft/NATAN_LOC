<!-- THE GENERATED WIDGET CONTAINER -->
<div class="border border-slate-400 bg-white rounded-sm shadow-md overflow-hidden">
    <!-- Widget Header -->
    <div class="bg-slate-100 border-b border-slate-300 px-3 py-2 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <span class="font-mono text-xs font-bold uppercase tracking-wide text-slate-700">{{ __('natan.genui.generated_tool_label') }}: Calcolatore CUP 2024</span>
        </div>
        <div class="text-[9px] font-mono text-slate-400">{{ __('natan.genui.auto_gen_id') }}: #9921</div>
    </div>

    <!-- Widget Body (The "App") -->
    <div class="p-4 gen-ui-pattern grid grid-cols-2 gap-4">
        
        <!-- Input: Superficie -->
        <div>
            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Superficie (mq)</label>
            <input type="number" value="12" class="border border-slate-400 rounded-[2px] p-1 px-2 font-mono text-xs w-full bg-white focus:outline-none focus:border-ink focus:ring-1 focus:ring-ink">
        </div>

        <!-- Input: Giorni -->
        <div>
            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Durata (giorni)</label>
            <input type="number" value="45" class="border border-slate-400 rounded-[2px] p-1 px-2 font-mono text-xs w-full bg-white focus:outline-none focus:border-ink focus:ring-1 focus:ring-ink">
        </div>

        <!-- Input: Zona -->
        <div class="col-span-2">
            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Zona Tariffaria</label>
            <select class="border border-slate-400 rounded-[2px] p-1 px-2 font-mono text-xs w-full bg-white focus:outline-none focus:border-ink focus:ring-1 focus:ring-ink cursor-pointer">
                <option value="A" selected>ZONA A (Centro Storico) - € 2,50 /mq/gg</option>
                <option value="B">ZONA B (Viali) - € 1,80 /mq/gg</option>
                <option value="C">ZONA C (Periferia) - € 1,10 /mq/gg</option>
            </select>
        </div>

        <!-- Input: Tipologia -->
        <div class="col-span-2">
            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Tipologia Occupazione</label>
            <div class="flex gap-4 text-xs font-sans bg-white p-2 border border-slate-300 rounded-sm">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="tipo" class="accent-black" checked> Cantiere Edile
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="tipo" class="accent-black"> Dehor / Tavolini
                </label>
            </div>
        </div>

        <!-- Result Box -->
        <div class="col-span-2 mt-2 border-t-2 border-slate-800 pt-3 flex justify-between items-end">
            <div>
                <div class="text-[10px] text-slate-500">{{ __('natan.genui.calculated_estimate') }}</div>
                <div class="text-xs font-mono text-slate-600">12mq * 45gg * €2,50</div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-mono font-bold text-ink">€ 1.350,00</div>
            </div>
        </div>

    </div>

    <!-- Widget Footer / Actions -->
    <div class="bg-slate-50 border-t border-slate-300 p-3 flex justify-end gap-2">
        <button class="px-3 py-1 text-xs font-bold uppercase border border-slate-300 bg-white hover:bg-slate-100 transition-colors rounded-sm">
            {{ __('natan.genui.copy_data') }}
        </button>
        <button class="px-3 py-1 text-xs font-bold uppercase bg-emerald-600 text-white hover:bg-emerald-700 transition-colors rounded-sm shadow-sm flex items-center gap-2">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"></path></svg>
            {{ __('natan.genui.save_to_dossier') }}
        </button>
    </div>
</div>

