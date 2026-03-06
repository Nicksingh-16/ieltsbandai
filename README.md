# 🎓 IELTS Band AI

<div align="center">

![IELTS Band AI](https://img.shields.io/badge/IELTS-Band%20AI-blue?style=for-the-badge)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)

**AI-Powered IELTS Writing & Speaking Evaluation Platform**

[Features](#-features) • [Demo](#-demo) • [Installation](#-installation) • [Tech Stack](#-tech-stack) • [Deployment](#-deployment) • [Contributing](#-contributing)

</div>

---

## 📖 About

**IELTS Band AI** is an intelligent platform that helps IELTS test-takers improve their writing and speaking skills through AI-powered evaluation. Using advanced GPT-4 technology and speech recognition, the platform provides detailed feedback, band score predictions, and personalized improvement suggestions.

### ✨ Key Features

- **📝 Writing Evaluation**
  - AI-powered essay analysis using GPT-4
  - Detailed error detection and correction
  - Band score prediction (0-9 scale)
  - Grammar, vocabulary, coherence, and task achievement feedback
  - Real-time inline error highlighting

- **🎤 Speaking Assessment**
  - Speech-to-text transcription (AssemblyAI/Deepgram)
  - Pronunciation and fluency analysis
  - Vocabulary range assessment
  - Speaking band score estimation

- **👤 User Management**
  - Email/password authentication with Laravel Breeze
  - Google OAuth social login
  - Credit-based system (3 free credits for new users)
  - Test history and progress tracking

- **💳 Payment Integration**
  - Razorpay payment gateway (Indian market)
  - Secure credit purchase system
  - Webhook-based payment verification

- **📊 Results & Reports**
  - Comprehensive evaluation reports
  - Downloadable result cards
  - Social sharing (WhatsApp, Telegram)
  - Print-friendly report generation

## 🎬 Demo

> **Note**: Add screenshots or GIF demos here once deployed

```bash
# Coming soon: Live demo link
```

## 🚀 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & npm
- MySQL 8.0+
- OpenAI API key
- AssemblyAI or Deepgram API key (for speaking tests)

### Local Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/Nicksingh-16/ieltsbandai.git
   cd ieltsbandai
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your `.env` file**
   - Set database credentials
   - Add OpenAI API key
   - Add AssemblyAI/Deepgram API key
   - Configure Google OAuth credentials
   - Add Razorpay keys (for payments)

5. **Database setup**
   ```bash
   php artisan migrate
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start development servers**
   ```bash
   # Terminal 1: Laravel server
   php artisan serve

   # Terminal 2: Vite dev server
   npm run dev
   ```

8. **Visit the application**
   ```
   http://localhost:8000
   ```

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 12
- **Authentication**: Laravel Breeze + Socialite
- **Database**: MySQL
- **Queue**: Database driver
- **Cache**: Database driver

### Frontend
- **CSS Framework**: Tailwind CSS 3
- **JavaScript**: Alpine.js
- **Build Tool**: Vite
- **Icons**: Heroicons

### AI & APIs
- **AI Model**: OpenAI GPT-4
- **Speech-to-Text**: AssemblyAI / Deepgram
- **Payment Gateway**: Razorpay

## 📁 Project Structure

```
ieltsbandai/
├── app/
│   ├── Http/Controllers/      # Application controllers
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic services
│   └── ...
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── resources/
│   ├── views/                 # Blade templates
│   ├── js/                    # JavaScript files
│   └── css/                   # Stylesheets
├── routes/
│   ├── web.php                # Web routes
│   └── api.php                # API routes
├── tests/                     # PHPUnit tests
└── public/                    # Public assets
```

## 🌐 Deployment

### Railway (Recommended)

1. **Create a Railway account** at [railway.app](https://railway.app)

2. **Create a new project** and add MySQL database

3. **Connect your GitHub repository**

4. **Configure environment variables** (copy from `.env.example`)

5. **Deploy**
   ```bash
   # Railway will automatically detect Laravel and deploy
   ```

For detailed deployment instructions, see [DEPLOYMENT.md](DEPLOYMENT.md)

### Alternative Platforms
- **Render**: See [deployment guide](DEPLOYMENT.md#render)
- **Fly.io**: See [deployment guide](DEPLOYMENT.md#flyio)
- **Vercel**: See [deployment guide](DEPLOYMENT.md#vercel)

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

Run code style checks:

```bash
./vendor/bin/pint --test
```

## 📚 API Documentation

For detailed API documentation, see [docs/API.md](docs/API.md)

### Quick Example

```bash
# Evaluate an essay
POST /api/evaluate
Content-Type: application/json

{
  "essay": "Your essay text here...",
  "task_type": "task2"
}
```

## 🤝 Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

### Development Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework
- [OpenAI](https://openai.com) - GPT-4 API
- [AssemblyAI](https://www.assemblyai.com) - Speech recognition
- [Tailwind CSS](https://tailwindcss.com) - CSS framework

## 📧 Contact

For questions or support, please open an issue or contact [your-email@example.com](mailto:your-email@example.com)

---

<div align="center">
Made with ❤️ for IELTS test-takers worldwide
</div>
