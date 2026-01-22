# 🚀 Deployment Guide

This guide covers deploying IELTS Band AI to various free hosting platforms.

## Table of Contents

- [Railway (Recommended)](#railway-recommended)
- [Render](#render)
- [Environment Variables](#environment-variables)
- [Post-Deployment](#post-deployment)
- [Troubleshooting](#troubleshooting)

---

## Railway (Recommended)

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

### Railway Configuration Files

The repository includes:
- `Procfile` - Defines the web process
- `railway.json` - Build and deploy configuration
- `nixpacks.toml` - PHP and Node.js versions

---

## Render

Render is another excellent free hosting option.

### Steps

1. **Create Render Account**
   - Sign up at [render.com](https://render.com)

2. **Create Web Service**
   - Click "New +" → "Web Service"
   - Connect your GitHub repository

3. **Configure Service**
   - **Name**: `ieltsbandai`
   - **Environment**: `Docker` or `Native`
   - **Build Command**:
     ```bash
     composer install --no-dev --optimize-autoloader && npm install && npm run build
     ```
   - **Start Command**:
     ```bash
     php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
     ```

4. **Add PostgreSQL Database** (Free tier)
   - Click "New +" → "PostgreSQL"
   - Copy the internal database URL

5. **Set Environment Variables**
   - Add all variables from [Environment Variables](#environment-variables)
   - Update `DB_CONNECTION=pgsql` for PostgreSQL

6. **Deploy**
   - Click "Create Web Service"
   - Wait for deployment

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

**For Railway MySQL:**
```bash
DB_CONNECTION=mysql
DB_HOST=${MYSQL_HOST}
DB_PORT=${MYSQL_PORT}
DB_DATABASE=${MYSQL_DATABASE}
DB_USERNAME=${MYSQL_USER}
DB_PASSWORD=${MYSQL_PASSWORD}
```

**For Render PostgreSQL:**
```bash
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
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

**Railway:**
- Go to Settings → Domains
- Add your custom domain
- Update DNS records as instructed

**Render:**
- Go to Settings → Custom Domain
- Add your domain
- Update DNS with provided CNAME

### 4. Set Up Monitoring

**Railway:**
- Built-in metrics available in dashboard
- View logs in real-time

**Render:**
- Check "Logs" tab for application logs
- Set up alerts in Settings

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
- For Railway: Use internal database URL variables

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

## Performance Optimization

### Enable Caching

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Queue Configuration

For better performance, use Redis for queues:

```bash
QUEUE_CONNECTION=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
```

### CDN for Assets

Consider using a CDN for static assets:
- Cloudflare (free tier)
- AWS CloudFront
- Vercel Edge Network

---

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secure
- [ ] API keys stored as environment variables
- [ ] HTTPS enabled (automatic on Railway/Render)
- [ ] CORS configured properly
- [ ] Rate limiting enabled
- [ ] Input validation on all forms

---

## Need Help?

- Check [Railway Docs](https://docs.railway.app)
- Check [Render Docs](https://render.com/docs)
- Open an issue on GitHub
- Review Laravel [Deployment Documentation](https://laravel.com/docs/deployment)

---

**Happy Deploying! 🚀**
