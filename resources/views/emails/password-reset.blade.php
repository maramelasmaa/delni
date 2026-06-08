<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.password_reset_subject') }}</title>
</head>

<body style="margin:0; padding:0; background:#f7f7f7; font-family:Arial, Helvetica, sans-serif; color:#333;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f7f7f7; padding:24px 12px;">
        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px; background:#ffffff; border-radius:8px; overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="background:#003366; padding:36px 20px;">
                            <div style="font-size:26px; font-weight:bold; color:#F1620F; margin-bottom:8px;">
                                دلني
                            </div>
                            <div style="font-size:14px; color:#ffffff;">
                                {{ __('auth.password_reset_subject') }}
                            </div>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:36px 28px; text-align:right;">

                            <h2 style="margin:0 0 18px; font-size:20px; color:#003366;">
                                {{ __('messages.hello', ['name' => $userName]) }}
                            </h2>

                            <p style="margin:0 0 24px; font-size:15px; line-height:1.8; color:#555;">
                                {{ __('messages.reset_password_message') }}
                            </p>

                            <div style="text-align:center; margin:32px 0;">
                                <a href="{{ $resetLink }}"
                                   style="display:inline-block; background:#F1620F; color:#ffffff; text-decoration:none; padding:14px 34px; border-radius:6px; font-size:16px; font-weight:bold;">
                                    {{ __('auth.reset_password_button') }}
                                </a>
                            </div>

                            <p style="margin:0 0 18px; font-size:14px; line-height:1.7; color:#666;">
                                {{ __('messages.reset_link_expires') }}
                            </p>

                            <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:5px; padding:14px; margin:22px 0; color:#856404; font-size:13px; line-height:1.7;">
                                <strong>{{ __('messages.security_warning') }}</strong><br>
                                {{ __('messages.reset_link_warning') }}
                            </div>

                            <p style="margin:22px 0 10px; font-size:13px; color:#666;">
                                {{ __('messages.reset_link_copy') }}
                            </p>

                            <div dir="ltr" style="background:#f9f9f9; border-radius:5px; padding:14px; font-size:12px; color:#777; word-break:break-all; text-align:left; font-family:Courier New, monospace;">
                                {{ $resetLink }}
                            </div>

                            <hr style="border:none; border-top:1px solid #eee; margin:28px 0;">

                            <p style="margin:0; font-size:13px; line-height:1.7; color:#666;">
                                {{ __('messages.reset_link_not_requested') }}
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background:#f9f9f9; border-top:1px solid #eee; padding:26px 20px; font-size:12px; color:#999;">

                            <p style="margin:0 0 10px;">
                                {{ __('messages.email_footer_text') }}
                            </p>

                            <p style="margin:10px 0; font-size:11px;">
                                <a href="{{ config('app.url') }}" style="color:#F1620F; text-decoration:none;">
                                    {{ config('app.name') }}
                                </a>
                                &nbsp;•&nbsp;
                                <a href="{{ url('/privacy') }}" style="color:#F1620F; text-decoration:none;">
                                    {{ __('messages.privacy_policy') }}
                                </a>
                                &nbsp;•&nbsp;
                                <a href="{{ url('/terms') }}" style="color:#F1620F; text-decoration:none;">
                                    {{ __('messages.terms_of_service') }}
                                </a>
                            </p>

                            <p style="margin:0; font-size:11px; color:#bbb;">
                                © {{ date('Y') }} {{ config('app.name') }}. {{ __('messages.all_rights_reserved') }}
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
