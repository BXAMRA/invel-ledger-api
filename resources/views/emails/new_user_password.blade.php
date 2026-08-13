<x-email-layout>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
        <h2 style="color: #2563eb;">Welcome to Invel LEDGER, {{ $user->name }}!</h2>

        <p>An account has been created for you. You can log in using the details below:</p>

        <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Username:</strong> {{ $user->username }}</p>
            <p style="margin: 5px 0;"><strong>Email:</strong> {{ $user->email }}</p>
            <p style="margin: 5px 0;"><strong>Temporary Password:</strong> <code style="font-size: 1.1em; background: #e5e7eb; padding: 2px 6px; border-radius: 4px;">{{ $password }}</code></p>
        </div>

        <p>Please log in and change your password as soon as possible from the settings tab.</p>

        <p>Best regards,<br>
        The Team</p>
    </div>
</x-email-layout>
