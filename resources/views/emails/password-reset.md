@component('mail::message')

# {{ __('auth.password_reset_subject') }}

{{ __('messages.hello', ['name' => $userName]) }}

{{ __('messages.reset_password_message') }}

@component('mail::button', ['url' => $resetLink])
{{ __('auth.reset_password_button') }}
@endcomponent

{{ __('messages.reset_link_expires') }}

**{{ __('messages.security_warning') }}**  
{{ __('messages.reset_link_warning') }}

{{ __('messages.reset_link_copy') }}

<{{ $resetLink }}>

---

{{ __('messages.reset_link_not_requested') }}

@endcomponent
