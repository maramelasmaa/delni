<!DOCTYPE html>
<html lang="ar" dir="rtl" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-wrapper { padding: 16px !important; }
            .email-container { width: 100% !important; }
            .email-body { padding: 28px 24px 24px !important; }
            .email-header, .email-footer { padding: 24px !important; }
            .btn-cta { padding: 14px 32px !important; font-size: 15px !important; }
        }
    </style>
</head>
<body dir="rtl" style="margin:0;padding:0;background-color:#F0F4F9;font-family:'Segoe UI',Tahoma,Arial,sans-serif;-webkit-font-smoothing:antialiased;mso-line-height-rule:exactly;">

    {{-- Hidden preheader — controls the preview text in inbox list --}}
    <div style="display:none;max-height:0;overflow:hidden;color:transparent;visibility:hidden;opacity:0;font-size:1px;line-height:1px;">
        تلقّينا طلب إعادة تعيين كلمة المرور لحسابك. الرابط صالح لمدة 60 دقيقة فقط.
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" class="email-wrapper" style="background-color:#F0F4F9;padding:40px 16px;">
        <tr>
            <td align="center">

                <table width="580" cellpadding="0" cellspacing="0" border="0" role="presentation" class="email-container" style="max-width:580px;width:100%;">

                    {{-- ── Header ── --}}
                    <tr>
                        <td class="email-header" style="background-color:#112240;border-radius:18px 18px 0 0;padding:32px 40px;text-align:center;">
                            <h1 style="margin:0;color:#E1AD01;font-size:36px;font-weight:900;letter-spacing:1px;line-height:1;">دلني</h1>
                            <p style="margin:6px 0 0;color:#8DA8CA;font-size:13px;font-weight:400;letter-spacing:0.3px;">منصة الخدمات المتخصصة</p>
                        </td>
                    </tr>

                    {{-- ── Body ── --}}
                    <tr>
                        <td class="email-body" style="background-color:#ffffff;padding:40px 40px 32px;">

                            {{-- Lock icon --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
                                <tr>
                                    <td align="center" style="padding-bottom:28px;">
                                        <div style="display:inline-block;background-color:#EFF6FF;border-radius:50%;width:68px;height:68px;line-height:68px;text-align:center;font-size:30px;">🔐</div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Greeting --}}
                            <h2 style="margin:0 0 16px;color:#112240;font-size:20px;font-weight:700;text-align:right;direction:rtl;line-height:1.4;">
                                مرحباً @if(!empty($userName)){{ $userName }}، @else ، @endif
                            </h2>

                            {{-- Main message --}}
                            <p style="margin:0 0 10px;color:#374151;font-size:15px;line-height:28px;text-align:right;direction:rtl;">
                                تلقّينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك على منصة <strong style="color:#112240;">دلني</strong>.
                            </p>
                            <p style="margin:0 0 32px;color:#374151;font-size:15px;line-height:28px;text-align:right;direction:rtl;">
                                اضغط على الزر أدناه لإعادة تعيين كلمة المرور. صلاحية هذا الرابط
                                <strong style="color:#1E40AF;">60 دقيقة</strong> فقط.
                            </p>

                            {{-- CTA Button — table-based for Outlook --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
                                <tr>
                                    <td align="center" style="padding-bottom:32px;">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word"
                                            href="{{ $url }}"
                                            style="height:52px;v-text-anchor:middle;width:260px;"
                                            arcsize="35%" stroke="f" fillcolor="#1E40AF">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:16px;font-weight:700;">
                                            إعادة تعيين كلمة المرور
                                        </center>
                                        </v:roundrect><![endif]-->
                                        <!--[if !mso]><!-->
                                        <a href="{{ $url }}"
                                           class="btn-cta"
                                           style="background-color:#1E40AF;border-radius:18px;color:#ffffff;display:inline-block;font-family:'Segoe UI',Tahoma,Arial,sans-serif;font-size:16px;font-weight:700;line-height:1;padding:16px 48px;text-decoration:none;direction:rtl;mso-hide:all;">
                                            إعادة تعيين كلمة المرور
                                        </a>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>

                            {{-- Fallback URL box --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin-bottom:28px;">
                                <tr>
                                    <td style="background-color:#F9F8F8;border-radius:12px;padding:16px 20px;">
                                        <p style="margin:0 0 6px;color:#6B7280;font-size:13px;text-align:right;direction:rtl;">
                                            إذا لم يعمل الزر، انسخ هذا الرابط وألصقه في متصفحك:
                                        </p>
                                        <a href="{{ $url }}" style="color:#1F4A7A;font-size:12px;direction:ltr;display:block;text-align:left;word-break:break-all;text-decoration:none;">
                                            {{ $url }}
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            {{-- Security disclaimer --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
                                <tr>
                                    <td style="border-top:1px solid #E7E7E7;padding-top:20px;">
                                        <p style="margin:0;color:#9CA3AF;font-size:13px;line-height:22px;text-align:right;direction:rtl;">
                                            إذا لم تطلب إعادة تعيين كلمة المرور، تجاهل هذا البريد تماماً. حسابك بأمان تام ولن يُجرى أي تغيير.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- ── Footer ── --}}
                    <tr>
                        <td class="email-footer" style="background-color:#112240;border-radius:0 0 18px 18px;padding:24px 40px;text-align:center;">
                            <p style="margin:0 0 6px;color:#8DA8CA;font-size:13px;direction:rtl;line-height:22px;">
                                هذا البريد أُرسل تلقائياً من منصة دلني، يُرجى عدم الرد عليه.
                            </p>
                            <p style="margin:0;color:#416EA6;font-size:12px;">
                                &copy; {{ date('Y') }} دلني. جميع الحقوق محفوظة.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
