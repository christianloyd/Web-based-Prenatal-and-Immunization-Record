 Google Drive Integration Setup Guide

 1. Google Cloud Console Setup

 Step 1: Create Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click "Select a project" → "New Project"
3. Project name: "Healthcare Backup System"
4. Click "Create"

 Step 2: Enable Google Drive API
1. Go to "APIs & Services" → "Library"
2. Search for "Google Drive API"
3. Click "Google Drive API" → "Enable"

 Step 3: Create Service Account
1. Go to "APIs & Services" → "Credentials"
2. Click "Create Credentials" → "Service account"
3. Service account name: "healthcare-backup"
4. Service account ID: "healthcare-backup"
5. Click "Create and Continue"
6. Skip roles (click "Continue")
7. Skip user access (click "Done")

 Step 4: Create JSON Key
1. Click on the created service account
2. Go to "Keys" tab
3. Click "Add Key" → "Create new key"
4. Select "JSON" → "Create"
5. Download the JSON file

 2. Google Drive Setup

 Step 1: Create Backup Folder
1. Go to [Google Drive](https://drive.google.com/)
2. Create a new folder: "Healthcare Backups"

 Step 2: Share Folder with Service Account
1. Right-click the "Healthcare Backups" folder
2. Click "Share"
3. Add the service account email (from JSON file: `client_email`)
4. Set permission to "Editor"
5. Click "Send"

 3. Laravel Configuration

 Step 1: Install Google Client
```bash
composer require google/apiclient
```

 Step 2: Add Credentials File
1. Copy your downloaded JSON file
2. Paste content into: `storage/app/google/credentials.json`
3. Make sure the file has proper JSON structure

 Step 3: Set File Permissions
```bash
chmod 600 storage/app/google/credentials.json
```

 4. Test Connection

Visit your backup page and check:
- ✅ Green "Connected to Google Drive" status
- ✅ Storage quota information displayed
- ✅ Can create backups successfully

 5. Security Notes

- ⚠️ Never commit credentials.json to version control
- ⚠️ Add to .gitignore: `storage/app/google/credentials.json`
- ⚠️ Use environment variables for production

 Troubleshooting

 Connection Failed
1. Check credentials.json format
2. Verify service account email
3. Ensure folder is shared with service account
4. Check Google Drive API is enabled

 Upload Failed
1. Check folder permissions
2. Verify storage quota not exceeded
3. Check PHP fileinfo extension enabled

 Download Failed
1. Verify file exists in Google Drive
2. Check service account has read access
3. Ensure temp directory is writable