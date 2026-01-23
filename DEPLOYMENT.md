# 🚀 Deployment Guide

This guide covers deploying IELTS Band AI to **Render** (Recommended) and other platforms.

## Table of Contents

- [Render Deployment](#render-deployment)
- [Railway](#railway)
- [Environment Variables](#environment-variables)
- [Post-Deployment](#post-deployment)
- [Troubleshooting](#troubleshooting)

---

## Render Deployment

We use a `render.yaml` file to automate deployment.

### Prerequisites

- GitHub account
- Render account ([render.com](https://render.com))

### Steps

1. **Create Render Account**
   - Sign up at [render.com](https://render.com)
   - Connect your GitHub account

2. **Create New Blueprint Instance**
   - Click "New +" → "Blueprint"
   - Connect your `ieltsbandai` repository
   - Render will automatically detect `render.yaml`

3. **Configure Resources**
   - **Service Name**: `ieltsbandai`
   - **Database Name**: `ieltsbandai-db`
   - Click "Apply" to start deployment

4. **Environment Variables**
   - You will be prompted to enter environment variables
   - See [Environment Variables](#environment-variables) section below for values

5. **Wait for Deployment**
   - Render will build your app and provision a PostgreSQL database
   - This may take 5-10 minutes

6. **Finalize Setup**
   - Once deployed, your app will be live at `https://ieltsbandai.onrender.com`

---

## Railway

Railway offers the easiest deployment experience with a generous free tier.

### Prerequisites

- GitHub account
- Railway account ([railway.app](https://railway.app))

### Steps

1. **Create Railway Account**
   - Sign up at [railway.app](https://railway.app)
   - Connect your GitHub account

2. **Create New Project**
   - Click "New Project"
   - Select "Deploy from GitHub repo"
   - Choose your `ieltsbandai` repository

3. **Add MySQL Database**
   - In your project, click "New"
   - Select "Database" → "MySQL"
   - Railway will automatically provision a MySQL instance

4. **Configure Environment Variables**
   - Click on your web service
   - Go to "Variables" tab
   - Add all variables from [Environment Variables](#environment-variables) section

5. **Deploy**
   - Railway will automatically detect Laravel
   - Build and deployment will start automatically
   - Wait for deployment to complete (~5-10 minutes)

6. **Run Migrations**
   - Go to your service settings
   - Under "Deploy", add to build command:
     ```bash
     php artisan migrate --force
     ```

7. **Get Your URL**
   - Click "Settings" → "Generate Domain"
   - Your app will be available at `https://your-app.up.railway.app`

---

## Environment Variables

Configure these environment variables on your hosting platform:

### Application

```bash
APP_NAME=IeltsBandAI
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY  # Generate with: php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://your-app-url.com
```

### Database

**For Render PostgreSQL:**
```bash
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
```

**For Railway MySQL:**
```bash
DB_CONNECTION=mysql
DB_HOST=${MYSQL_HOST}
DB_PORT=${MYSQL_PORT}
DB_DATABASE=${MYSQL_DATABASE}
DB_USERNAME=${MYSQL_USER}
DB_PASSWORD=${MYSQL_PASSWORD}
```

### Session & Cache

```bash
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### API Keys

```bash
# OpenAI (Required)
OPENAI_API_KEY=sk-proj-your-openai-key
OPENAI_MODEL=gpt-4o

# Speech-to-Text (Choose one)
TRANSCRIPTION_PROVIDER=assemblyai
ASSEMBLYAI_API_KEY=your-assemblyai-key
# OR
# DEEPGRAM_API_KEY=your-deepgram-key

# Razorpay (For payments)
RAZORPAY_KEY=your-razorpay-key
RAZORPAY_SECRET=your-razorpay-secret
RAZORPAY_WEBHOOK_SECRET=your-webhook-secret

# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://your-app-url.com/auth/google/callback
```

### Logging

```bash
LOG_CHANNEL=stack
LOG_LEVEL=error
IELTS_DEBUG=false
```

---

## Post-Deployment

### 1. Update Google OAuth

Update your Google Cloud Console with the production redirect URI:

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Select your project
3. Navigate to "APIs & Services" → "Credentials"
4. Edit your OAuth 2.0 Client ID
5. Add authorized redirect URI:
   ```
   https://your-app-url.com/auth/google/callback
   ```

### 2. Test the Application

- [ ] Visit your deployed URL
- [ ] Register a new account
- [ ] Test Google OAuth login
- [ ] Upload a test essay
- [ ] Verify AI evaluation works
- [ ] Test payment flow (if configured)

### 3. Configure Custom Domain (Optional)

**Render:**
- Go to Settings → Custom Domain
- Add your domain
- Update DNS with provided CNAME

**Railway:**
- Go to Settings → Domains
- Add your custom domain
- Update DNS records as instructed

### 4. Set Up Monitoring

**Render:**
- Check "Logs" tab for application logs
- Set up alerts in Settings

**Railway:**
- Built-in metrics available in dashboard
- View logs in real-time

---

## Troubleshooting

### Build Fails

**Issue**: Composer or NPM installation fails

**Solution**:
```bash
# Ensure composer.json and package.json are committed
# Check PHP version (must be 8.2+)
# Verify all dependencies are listed
```

### Database Connection Error

**Issue**: `SQLSTATE[HY000] [2002] Connection refused`

**Solution**:
- Verify database environment variables
- Ensure database service is running
- Check if migrations ran successfully
- For Render: Ensure you're using the "Internal Connection URL" if configuring manually, usually `host` is sufficient with environment variables.

### 500 Internal Server Error

**Issue**: White screen or 500 error

**Solution**:
1. Check logs on your hosting platform
2. Ensure `APP_KEY` is set
3. Run migrations: `php artisan migrate --force`
4. Clear cache: `php artisan config:clear`
5. Set `APP_DEBUG=true` temporarily to see error details

### Assets Not Loading

**Issue**: CSS/JS files return 404

**Solution**:
- Ensure `npm run build` ran successfully
- Check if `public/build` directory exists
- Verify Vite configuration in `vite.config.js`
- Render detects `package.json` and runs build automatically if scripts present.

### Google OAuth Not Working

**Issue**: OAuth redirect fails

**Solution**:
- Verify `GOOGLE_REDIRECT_URI` matches exactly in:
  - `.env` file
  - Google Cloud Console
- Ensure HTTPS is used in production
- Check Google OAuth credentials are correct

### API Rate Limits

**Issue**: OpenAI API errors

**Solution**:
- Check your OpenAI API quota
- Verify API key is correct
- Consider implementing rate limiting
- Add error handling for API failures

---

## Need Help?

- Check [Render Docs](https://render.com/docs)
- Check [Railway Docs](https://docs.railway.app)
- Open an issue on GitHub
- Review Laravel [Deployment Documentation](https://laravel.com/docs/deployment)

---

**Happy Deploying! 🚀**
