<x-mail::message>
# {{ $mailSubject }}

{{ $bodyMessage }}

@if($actionUrl)
<x-mail::button :url="$actionUrl">
{{ $actionText }}
</x-mail::button>
@endif

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
