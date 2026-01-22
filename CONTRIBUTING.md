# Contributing to IELTS Band AI

Thank you for your interest in contributing to IELTS Band AI! We welcome contributions from the community.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:
- A clear, descriptive title
- Steps to reproduce the issue
- Expected vs actual behavior
- Your environment (PHP version, OS, browser)
- Screenshots if applicable

### Suggesting Features

Feature requests are welcome! Please:
- Check if the feature has already been requested
- Provide a clear use case
- Explain how it benefits users
- Consider implementation complexity

### Pull Requests

1. **Fork the repository** and create a new branch
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Follow Laravel coding standards
   - Write clear, commented code
   - Update documentation as needed

3. **Test your changes**
   ```bash
   php artisan test
   ./vendor/bin/pint
   ```

4. **Commit with clear messages**
   ```bash
   git commit -m "Add: Brief description of your changes"
   ```

5. **Push and create a PR**
   ```bash
   git push origin feature/your-feature-name
   ```

## Development Guidelines

### Code Style

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use Laravel Pint for code formatting: `./vendor/bin/pint`
- Write meaningful variable and function names
- Add comments for complex logic

### Testing

- Write tests for new features
- Ensure all tests pass before submitting PR
- Aim for good test coverage

### Commit Messages

Use conventional commit format:
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `style:` Code style changes (formatting)
- `refactor:` Code refactoring
- `test:` Adding or updating tests
- `chore:` Maintenance tasks

### Branch Naming

- `feature/` - New features
- `fix/` - Bug fixes
- `docs/` - Documentation updates
- `refactor/` - Code refactoring

## Development Setup

See [README.md](README.md#installation) for detailed setup instructions.

## Questions?

Feel free to open an issue for any questions or clarifications.

---

Thank you for contributing! 🎉
