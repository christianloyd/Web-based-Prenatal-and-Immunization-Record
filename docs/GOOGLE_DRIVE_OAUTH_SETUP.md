 Google Drive OAuth2 Setup Guide

 🚀 Healthcare Backup System  Google Drive Integration

This guide will help you set up OAuth2 authentication for Google Drive backups in your healthcare system.



 📋 Prerequisites

 Google Cloud Console account
 Healthcare backup system installed
 Admin access to the application



 🔧 Step 1: Google Cloud Console Setup

 1.1 Create/Select Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Make sure billing is enabled (required for APIs)

 1.2 Enable Google Drive API
1. Navigate to "APIs & Services" > "Library"
2. Search for "Google Drive API"
3. Click on it and press "Enable"

 1.3 Configure OAuth Consent Screen
1. Go to "APIs & Services" > "OAuth consent screen"
2. Choose "External" (unless you have Google Workspace)
3. Fill in required information:
    App name: Healthcare Backup System
    User support email: Your email
    Developer contact: Your email
4. Add scopes: "../auth/drive.file"
5. Add test users: Your email address
6. Save and continue

 1.4 Create OAuth Client ID
1. Navigate to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth client ID"
3. Choose "Web application" as application type
4. Give it a name: "Healthcare Backup"
5. Add Authorized redirect URIs:
   ```
   http://localhost/healthcare/public/google/callback
   http://localhost:8000/google/callback
   https://yourdomain.com/healthcare/public/google/callback
   https://www.yourdomain.com/healthcare/public/google/callback
   https://yourdomain.com/google/callback
   ```
6. Click "Create"

 1.5 Download Credentials
1. A popup will show your Client ID and Client Secret
2. Click "Download JSON" button
3. Save the file as `oauth_credentials.json`



 📁 Step 2: Install Credentials

 2.1 File Placement
1. Place the `oauth_credentials.json` file in:
   ```
   storage/app/google/oauth_credentials.json
   ```

 2.2 File Structure
The JSON should look like this:
```json
{
  "web": {
    "client_id": "yourclientid.googleusercontent.com",
    "project_id": "yourprojectid",
    "auth_uri": "https://accounts.google.com/o/oauth2/auth",
    "token_uri": "https://oauth2.googleapis.com/token",
    "client_secret": "yourclientsecret",
    "redirect_uris": ["http://localhost/healthcare/public/google/callback"]
  }
}
```



 ⚙️ Step 3: Environment Configuration

 3.1 For Localhost Development (.env)
```env
 Leave empty  autodetects localhost
 GOOGLE_REDIRECT_URI=
```

 3.2 For Production (.env)
```env
 Specify your production domain
GOOGLE_REDIRECT_URI=https://yourdomain.com/healthcare/public/google/callback
```



 🎯 Step 4: Testing the Setup

 4.1 Access Cloud Backup Page
1. Login to your healthcare system as midwife
2. Navigate to Cloud Backup section
3. You should see "Google Drive Authentication Required" status

 4.2 Authenticate with Google
1. Click "Connect Google Drive" button
2. You'll be redirected to Google's authentication page
3. Sign in with your Google account
4. Grant permissions to the healthcare app
5. You'll be redirected back with success message

 4.3 Verify Connection
 Status should show "Google Drive Connected"
 Green indicator with "Connected" status
 "Disconnect" button should be visible



 🌐 Deployment Scenarios

 Scenario 1: XAMPP/Localhost Development
 ✅ Works automatically with current setup
 ✅ Autodetects `http://localhost/healthcare/public/google/callback`
 ✅ No additional configuration needed

 Scenario 2: Shared Hosting (cPanel)
 ✅ Upload all files to `public_html`
 ✅ Set `GOOGLE_REDIRECT_URI` in `.env`
 ✅ Same OAuth credentials work

 Scenario 3: VPS/Cloud Server
 ✅ Configure Apache/Nginx
 ✅ Set up SSL certificate
 ✅ Update `GOOGLE_REDIRECT_URI`

 Scenario 4: Development → Production
 ✅ Same OAuth project works for both
 ✅ Just change environment variable
 ✅ No code changes needed



 🔧 Advanced Configuration

 Custom Redirect URI
If you need a specific redirect pattern, set in `.env`:
```env
GOOGLE_REDIRECT_URI=https://custom.domain.com/custom/path/google/callback
```

 Multiple Environments
You can use the same Google Cloud project for:
 Development (localhost)
 Staging (staging.yourdomain.com)
 Production (yourdomain.com)

Just add all redirect URIs in Google Cloud Console.



 🚨 Troubleshooting

 Error: "redirect_uri_mismatch"
 Problem: The redirect URI doesn't match Google Cloud Console
 Solution: Check the redirect URIs in Google Cloud Console match exactly

 Error: "invalid_client"
 Problem: Wrong credentials or corrupted JSON
 Solution: Redownload credentials from Google Cloud Console

 Error: "access_denied"
 Problem: User denied permissions
 Solution: User needs to reauthenticate and grant permissions

 Connection shows "Offline"
 Problem: OAuth credentials not found or invalid
 Solution: Check `oauth_credentials.json` file placement and content

 Token expired errors
 Problem: Authentication token has expired
 Solution: System should autorefresh, or click "Disconnect" and reconnect



 📊 System Status Indicators

 ✅ Connected
 Status: Green with "Google Drive Connected"
 Description: Ready for cloud backups
 Action: Disconnect button available

 ⚠️ Authentication Required
 Status: Yellow with warning message
 Description: OAuth credentials found but not authenticated
 Action: "Connect Google Drive" button

 🔗 Service Account Mode
 Status: Green with "Service Account Mode"
 Description: Using legacy service account (has storage limitations)
 Action: Consider migrating to OAuth2

 ❌ Disconnected
 Status: Red with "Offline" indicator
 Description: No Google Drive connection
 Action: Local backups only



 🔐 Security Notes

 OAuth2 tokens are stored in `storage/app/google/token.json`
 Tokens are automatically refreshed when expired
 Use HTTPS in production for security
 Keep your client secret confidential
 Regularly review granted permissions in Google Account settings



 📞 Support

If you encounter issues:
1. Check Laravel logs in `storage/logs/laravel.log`
2. Verify all redirect URIs in Google Cloud Console
3. Ensure Google Drive API is enabled
4. Check file permissions on storage directory



 🎉 Success!

Once set up correctly, you'll be able to:
 ✅ Create backups that upload to your Google Drive
 ✅ Restore from cloud backups
 ✅ Manage connection status through the UI
 ✅ Automatic token management
 ✅ Works across all deployment environments

Your healthcare backup system is now ready for secure cloud storage! 🚀