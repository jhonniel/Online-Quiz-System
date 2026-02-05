<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0; font-size: 24px;">Task List Invitation</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <p style="font-size: 16px; margin-bottom: 20px;">
            Hello,
        </p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">
            <strong>{{ $inviterName }}</strong> has invited you to join the task list: <strong>{{ $taskListName }}</strong>
        </p>
        
        <div style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; margin: 20px 0;">
            <h2 style="color: #667eea; margin-top: 0; font-size: 18px;">How to Join:</h2>
            
            @if($inviteCode)
            <div style="margin-bottom: 20px;">
                <p style="margin: 10px 0; font-weight: bold;">Option 1: Use Invite Code</p>
                <div style="background: #f3f4f6; padding: 15px; border-radius: 6px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 2px; color: #667eea; font-family: 'Courier New', monospace;">
                    {{ $inviteCode }}
                </div>
                <p style="font-size: 14px; color: #6b7280; margin-top: 10px;">
                    Go to your tasks page and enter this code to join the task list.
                </p>
            </div>
            @endif
            
            @if($shareLink)
            <div>
                <p style="margin: 10px 0; font-weight: bold;">Option 2: Use Share Link</p>
                <div style="background: #f3f4f6; padding: 15px; border-radius: 6px; word-break: break-all;">
                    <a href="{{ $shareLink }}" style="color: #667eea; text-decoration: none; font-size: 14px;">
                        {{ $shareLink }}
                    </a>
                </div>
                <p style="font-size: 14px; color: #6b7280; margin-top: 10px;">
                    Click the link above to join the task list directly.
                </p>
            </div>
            @endif
        </div>
        
        <p style="font-size: 14px; color: #6b7280; margin-top: 30px;">
            <strong>Note:</strong> You must be logged in to your account to join this task list.
        </p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <p style="font-size: 14px; color: #6b7280; margin: 0;">
                If you did not expect this invitation, you can safely ignore this email.
            </p>
        </div>
    </div>
</body>
</html>
