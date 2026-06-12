<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">

    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="margin: 0; color: #F1620F; font-size: 28px;">🧪 Brevo SMTP Test</h1>
            <p style="margin: 10px 0 0; color: #666; font-size: 14px;">Delni Email Configuration Verification</p>
        </div>

        <div style="color: #333; line-height: 1.6;">
            <p>Hello,</p>

            <p>This is a <strong>test email</strong> from Delni's Brevo SMTP configuration.</p>

            <div style="background: #f0f7ff; border-left: 4px solid #F1620F; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; color: #0B1A34;">
                    <strong>✅ If you received this email, your Brevo SMTP is working correctly!</strong>
                </p>
            </div>

            <h2 style="color: #0B1A34; font-size: 16px; margin-top: 25px;">Configuration Details:</h2>
            <ul style="color: #666; font-size: 14px;">
                <li><strong>Service:</strong> Brevo (formerly Sendinblue)</li>
                <li><strong>Protocol:</strong> SMTP with TLS</li>
                <li><strong>Host:</strong> smtp-relay.brevo.com:587</li>
                <li><strong>Status:</strong> ✅ Verified</li>
            </ul>

            <h2 style="color: #0B1A34; font-size: 16px; margin-top: 25px;">Next Steps:</h2>
            <ol style="color: #666; font-size: 14px;">
                <li>Confirm you received this email</li>
                <li>Update your production environment variables with the Brevo credentials</li>
                <li>Deploy to Railway</li>
                <li>Start sending transactional emails</li>
            </ol>

            <div style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0; color: #e65100; font-size: 13px;">
                    <strong>💡 Tip:</strong> You can send up to 300 emails per day on Brevo's free plan.
                </p>
            </div>

            <p style="margin-top: 30px; color: #999; font-size: 12px;">
                This is an automated test email. Please do not reply to this message.
            </p>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #999; font-size: 12px;">
            <p style="margin: 0;">
                Delni Platform<br>
                <a href="{{ config('app.url') }}" style="color: #F1620F; text-decoration: none;">{{ config('app.url') }}</a>
            </p>
        </div>

    </div>

</div>
