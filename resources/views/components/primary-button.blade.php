<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-500 border border-transparent rounded-xl font-semibold text-sm text-white shadow-sm shadow-primary-500/25 hover:bg-primary-600 active:bg-primary-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
