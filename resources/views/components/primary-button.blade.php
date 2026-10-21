<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 shadow-soft shadow-primary-600/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 disabled:opacity-50 transition-all duration-200 active:scale-[0.98]']) }}>
    {{ $slot }}
</button>
