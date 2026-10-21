<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['aria-hidden' => 'true']) }}>
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#b89c84"/>
      <stop offset="100%" stop-color="#82604a"/>
    </linearGradient>
    <linearGradient id="roof" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0.95"/>
      <stop offset="100%" stop-color="#f0eeeb" stop-opacity="0.9"/>
    </linearGradient>
  </defs>
  <rect width="64" height="64" rx="16" fill="url(#bg)"/>
  <path d="M32 12L14 28V50C14 51.1 14.9 52 16 52H28V38C28 36.9 28.9 36 30 36H34C35.1 36 36 36.9 36 38V52H48C49.1 52 50 51.1 50 50V28L32 12Z" fill="url(#roof)"/>
  <circle cx="32" cy="30" r="3" fill="#a88468"/>
</svg>
