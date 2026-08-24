<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold text-sm text-slate-600 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
