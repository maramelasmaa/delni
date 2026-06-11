@component('mail::message')

# {{ __('auth.set_password_subject') }}

{{ __('messages.hello', ['name' => $userName]) }}

{{ __('messages.set_password_message') }}

@component('mail::button', ['url' => $setPasswordLink])
{{ __('auth.set_password_button') }}
@endcomponent

{{ __('messages.set_password_link_expires') }}

**{{ __('messages.set_password_info') }}**  
{{ __('messages.set_password_info_desc') }}

{{ __('messages.set_password_link_copy') }}

<{{ $setPasswordLink }}>

---

{{ __('messages.set_password_not_requested') }}

@endcomponent
