# Contributing to SmartPlants IoT

Thank you for considering contributing to SmartPlants IoT! 🌱

## 🚀 Quick Setup for Contributors

```bash
# 1. Fork and clone
git clone https://github.com/YOUR_USERNAME/SmartPlants-IOT.git
cd SmartPlants-IOT

# 2. Run setup script
# Windows:
setup.bat

# Linux/Mac:
chmod +x setup.sh
./setup.sh

# 3. Create a feature branch
git checkout -b feature/your-feature-name

# 4. Make your changes

# 5. Test
php artisan test

# 6. Commit and push
git add .
git commit -m "feat: your feature description"
git push origin feature/your-feature-name

# 7. Create Pull Request
```

## 📋 Development Guidelines

### Code Style

- Follow PSR-12 coding standards for PHP
- Use meaningful variable and function names
- Add comments for complex logic
- Keep functions small and focused

### Commit Messages

Use conventional commits format:

```
feat: add new sensor type support
fix: resolve device provisioning issue
docs: update setup guide
refactor: simplify device ID generation
test: add unit tests for provisioning
```

### Database Changes

- Always create migrations for schema changes
- Test migrations with both `up()` and `down()`
- Ensure migrations are idempotent (can run multiple times safely)

### Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter ProvisioningTest

# Run with coverage
php artisan test --coverage
```

## 🔧 Project Structure

```
SmartPlants-IOT/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/              # API endpoints
│   │   │   └── Dashboard/        # Web controllers
│   │   └── Middleware/           # Request middleware
│   ├── Models/                   # Eloquent models
│   └── Services/                 # Business logic
├── database/
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Seed data
├── esp8266/                      # ESP8266 firmware
├── resources/
│   ├── js/                       # Frontend JS
│   ├── css/                      # Styles
│   └── views/                    # Blade templates
├── routes/
│   ├── web.php                   # Web routes
│   └── api.php                   # API routes
├── docs/                         # Documentation
└── tests/                        # Test suite
```

## 🐛 Reporting Bugs

When reporting bugs, please include:

1. **Description**: Clear description of the issue
2. **Steps to Reproduce**: Detailed steps to reproduce the bug
3. **Expected Behavior**: What you expected to happen
4. **Actual Behavior**: What actually happened
5. **Environment**:
   - OS (Windows/Linux/Mac)
   - PHP version
   - Laravel version
   - Database (PostgreSQL/MySQL)
6. **Logs**: Relevant error messages or logs

## 💡 Suggesting Features

Feature requests are welcome! Please:

1. Check existing issues to avoid duplicates
2. Clearly describe the feature and its benefits
3. Provide use cases
4. Include mockups or examples if applicable

## 🔒 Security Issues

**Do NOT** report security vulnerabilities in public issues.

Instead, email: [security@yourproject.com]

## 📝 Pull Request Process

1. **Update Documentation**: Update relevant docs if needed
2. **Add Tests**: Include tests for new features
3. **Follow Style Guide**: Ensure code follows project conventions
4. **Update CHANGELOG**: Add entry to CHANGELOG.md
5. **One Feature Per PR**: Keep PRs focused on single feature/fix
6. **CI Must Pass**: Ensure all CI checks pass

### PR Title Format

```
feat: add multi-language support
fix: resolve device offline detection
docs: improve setup instructions
refactor: optimize database queries
test: add integration tests for API
```

## 🧪 Testing Guidelines

### Unit Tests

Test individual components in isolation:

```php
public function test_device_generates_unique_id()
{
    $device = Device::factory()->create([
        'user_id' => 1,
        'id' => 'user_1_chip_62563'
    ]);
    
    $this->assertTrue($device->isOwnedBy(1));
    $this->assertFalse($device->isOwnedBy(2));
}
```

### Feature Tests

Test complete features end-to-end:

```php
public function test_user_can_provision_device()
{
    $token = ProvisioningToken::factory()->create([
        'user_id' => 1
    ]);
    
    $response = $this->postJson('/api/provision/claim', [
        'token' => $token->token,
        'device_id' => '62563'
    ]);
    
    $response->assertStatus(200);
    $this->assertDatabaseHas('devices', [
        'id' => 'user_1_chip_62563'
    ]);
}
```

## 📚 Documentation

When adding features:

1. Update relevant `.md` files in `docs/`
2. Add code comments for complex logic
3. Update README.md if user-facing
4. Include examples in documentation

## 🔄 Development Workflow

1. **Sync Fork**: Keep your fork updated
   ```bash
   git remote add upstream https://github.com/kurokana/SmartPlants-IOT.git
   git fetch upstream
   git merge upstream/main
   ```

2. **Feature Branch**: Create from main
   ```bash
   git checkout main
   git pull origin main
   git checkout -b feature/my-feature
   ```

3. **Regular Commits**: Commit often
   ```bash
   git add .
   git commit -m "feat: implement feature X"
   ```

4. **Push and PR**: Push and create pull request
   ```bash
   git push origin feature/my-feature
   ```

## ✅ Checklist Before Submitting PR

- [ ] Code follows project style guidelines
- [ ] Added/updated tests
- [ ] All tests pass locally
- [ ] Updated documentation
- [ ] Updated CHANGELOG.md
- [ ] Commit messages follow convention
- [ ] No merge conflicts
- [ ] CI checks pass

## 🎯 Priority Areas

We're especially looking for contributions in:

- 🧪 **Testing**: Improve test coverage
- 📝 **Documentation**: Improve guides and examples
- 🌐 **Internationalization**: Multi-language support
- 📱 **Mobile App**: Mobile companion app
- 🔌 **Sensors**: Support for more sensor types
- 🎨 **UI/UX**: Dashboard improvements
- ⚡ **Performance**: Optimization and caching

## 📞 Getting Help

- **Documentation**: Check `docs/` folder
- **Issues**: Search existing issues
- **Discussions**: Use GitHub Discussions for questions
- **Discord**: [Join our Discord] (if applicable)

## 📄 License

By contributing, you agree that your contributions will be licensed under the same license as the project.

---

Thank you for contributing to SmartPlants IoT! 🌱✨
