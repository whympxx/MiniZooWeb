# 🤝 Contributing Guide

Thank you for your interest in contributing to the Zoo Management System! This guide will help you get started with contributing to our project.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contribution Workflow](#contribution-workflow)
- [Coding Standards](#coding-standards)
- [Testing Guidelines](#testing-guidelines)
- [Documentation](#documentation)
- [Pull Request Process](#pull-request-process)
- [Issue Reporting](#issue-reporting)
- [Community](#community)

## Code of Conduct

### Our Pledge

We are committed to making participation in our project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

Examples of behavior that contributes to creating a positive environment include:

- Using welcoming and inclusive language
- Being respectful of differing viewpoints and experiences
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

### Enforcement

Instances of abusive, harassing, or otherwise unacceptable behavior may be reported by contacting the project team at conduct@zoo-management.com.

## Getting Started

### Prerequisites

Before contributing, ensure you have:

- **PHP 7.4+** installed
- **MySQL/MariaDB** database server
- **Git** for version control
- **Text editor/IDE** (VS Code, PHPStorm, etc.)
- **XAMPP/WAMP/LAMP** for local development

### First-Time Setup

1. **Fork the Repository**
   ```bash
   # Fork on GitHub, then clone your fork
   git clone https://github.com/your-username/zoo-management.git
   cd zoo-management
   ```

2. **Add Upstream Remote**
   ```bash
   git remote add upstream https://github.com/zoo-management/zoo-management.git
   ```

3. **Install Dependencies**
   ```bash
   # If using Composer
   composer install
   
   # Set up environment
   cp .env.example .env
   ```

4. **Database Setup**
   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE zoo_management_dev;"
   
   # Import schema
   mysql -u root -p zoo_management_dev < database/setup_database.sql
   ```

## Development Setup

### Local Environment

1. **Configure Database**
   ```php
   // config.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'zoo_management_dev');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', '');
   define('DEBUG_MODE', true);
   ```

2. **Enable Error Reporting**
   ```php
   // For development
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

3. **Start Development Server**
   ```bash
   # Using PHP built-in server
   php -S localhost:8000
   
   # Or use XAMPP/WAMP
   # Access via http://localhost/zoo-management
   ```

### Development Tools

#### Code Quality Tools

```bash
# Install PHP CodeSniffer
composer global require "squizlabs/php_codesniffer=*"

# Install PHPUnit
composer require --dev phpunit/phpunit

# Install PHP-CS-Fixer
composer global require friendsofphp/php-cs-fixer
```

#### IDE Configuration

**VS Code Extensions:**
- PHP Intelephense
- PHP Debug
- GitLens
- PHP DocBlocker
- Bracket Pair Colorizer

**PHPStorm Settings:**
- Enable PSR-12 code style
- Configure PHP interpreter
- Set up database connection
- Enable Git integration

## Contribution Workflow

### Branch Strategy

We use **Git Flow** branching model:

```
main (production)
├── develop (integration)
│   ├── feature/user-authentication
│   ├── feature/ticket-booking
│   └── feature/admin-dashboard
├── release/v1.1.0
└── hotfix/security-patch
```

### Creating a Feature

1. **Create Feature Branch**
   ```bash
   git checkout develop
   git pull upstream develop
   git checkout -b feature/your-feature-name
   ```

2. **Make Changes**
   ```bash
   # Write code, tests, documentation
   git add .
   git commit -m "feat: add user authentication system"
   ```

3. **Keep Updated**
   ```bash
   git fetch upstream
   git rebase upstream/develop
   ```

4. **Push and Create PR**
   ```bash
   git push origin feature/your-feature-name
   # Create Pull Request on GitHub
   ```

### Commit Message Convention

We follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```bash
feat(auth): add two-factor authentication
fix(tickets): resolve booking date validation issue
docs(api): update endpoint documentation
test(user): add unit tests for user registration
```

## Coding Standards

### PHP Standards

We follow **PSR-12** coding standard:

```php
<?php

declare(strict_types=1);

namespace ZooManagement\Authentication;

use ZooManagement\Database\Connection;
use InvalidArgumentException;

/**
 * User authentication manager
 */
class AuthManager
{
    private Connection $database;
    
    public function __construct(Connection $database)
    {
        $this->database = $database;
    }
    
    /**
     * Authenticate user with email and password
     *
     * @param string $email User email address
     * @param string $password Plain text password
     * @return array|null User data if authenticated, null otherwise
     * @throws InvalidArgumentException If email format is invalid
     */
    public function authenticate(string $email, string $password): ?array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        
        $user = $this->database->select(
            'SELECT id, email, password_hash, role FROM users WHERE email = ? AND status = ?',
            [$email, 'active']
        );
        
        if (empty($user) || !password_verify($password, $user[0]['password_hash'])) {
            return null;
        }
        
        return $user[0];
    }
}
```

### HTML/CSS Standards

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title - Zoo Management</title>
    <!-- Use semantic HTML -->
</head>
<body>
    <header>
        <nav aria-label="Main navigation">
            <!-- Navigation content -->
        </nav>
    </header>
    
    <main>
        <section>
            <!-- Main content -->
        </section>
    </main>
    
    <footer>
        <!-- Footer content -->
    </footer>
</body>
</html>
```

### JavaScript Standards

```javascript
/**
 * User authentication handler
 */
class AuthHandler {
    constructor(apiEndpoint) {
        this.apiEndpoint = apiEndpoint;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    }
    
    /**
     * Login user with credentials
     * @param {string} email - User email
     * @param {string} password - User password
     * @returns {Promise<Object>} Response data
     */
    async login(email, password) {
        try {
            const response = await fetch(`${this.apiEndpoint}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify({ email, password })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Login failed:', error);
            throw error;
        }
    }
}
```

### Database Standards

```sql
-- Use descriptive table and column names
CREATE TABLE user_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ticket_type_id INT NOT NULL,
    booking_reference VARCHAR(50) UNIQUE NOT NULL,
    visit_date DATE NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraints
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id) ON DELETE RESTRICT,
    
    -- Indexes for performance
    INDEX idx_user_bookings_user_id (user_id),
    INDEX idx_user_bookings_visit_date (visit_date),
    INDEX idx_user_bookings_status (booking_status)
);
```

## Testing Guidelines

### Unit Testing

```php
<?php

use PHPUnit\Framework\TestCase;
use ZooManagement\Authentication\AuthManager;

class AuthManagerTest extends TestCase
{
    private AuthManager $authManager;
    private $mockDatabase;
    
    protected function setUp(): void
    {
        $this->mockDatabase = $this->createMock(Connection::class);
        $this->authManager = new AuthManager($this->mockDatabase);
    }
    
    public function testAuthenticateWithValidCredentials(): void
    {
        // Arrange
        $email = 'user@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $userData = [
            'id' => 1,
            'email' => $email,
            'password_hash' => $hashedPassword,
            'role' => 'user'
        ];
        
        $this->mockDatabase
            ->expects($this->once())
            ->method('select')
            ->willReturn([$userData]);
        
        // Act
        $result = $this->authManager->authenticate($email, $password);
        
        // Assert
        $this->assertNotNull($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals($email, $result['email']);
    }
    
    public function testAuthenticateWithInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        $this->authManager->authenticate('invalid-email', 'password123');
    }
}
```

### Integration Testing

```php
<?php

class TicketBookingIntegrationTest extends TestCase
{
    private $database;
    
    protected function setUp(): void
    {
        // Set up test database
        $this->database = new Connection($testDsn, $username, $password);
        $this->seedTestData();
    }
    
    public function testCompleteBookingWorkflow(): void
    {
        // Test the complete booking process
        $bookingData = [
            'user_id' => 1,
            'ticket_type_id' => 1,
            'visit_date' => '2024-12-25',
            'quantity' => 2
        ];
        
        // Create booking
        $bookingId = $this->createBooking($bookingData);
        $this->assertNotNull($bookingId);
        
        // Process payment
        $paymentResult = $this->processPayment($bookingId);
        $this->assertTrue($paymentResult);
        
        // Verify booking status
        $booking = $this->getBooking($bookingId);
        $this->assertEquals('confirmed', $booking['status']);
    }
}
```

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Unit/AuthManagerTest.php

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage/

# Run specific test group
vendor/bin/phpunit --group authentication
```

## Documentation

### Code Documentation

```php
/**
 * Calculate total price for ticket booking
 *
 * @param int $ticketTypeId The ID of the ticket type
 * @param int $quantity Number of tickets to book
 * @param string|null $discountCode Optional discount code
 * @return array{
 *     subtotal: float,
 *     discount: float,
 *     tax: float,
 *     total: float
 * } Price breakdown
 * @throws InvalidArgumentException If ticket type doesn't exist
 * @throws \Exception If discount code is invalid
 */
public function calculateTotalPrice(
    int $ticketTypeId, 
    int $quantity, 
    ?string $discountCode = null
): array {
    // Implementation
}
```

### API Documentation

```yaml
# OpenAPI 3.0 format
/api/tickets:
  post:
    summary: Create new ticket booking
    tags:
      - Tickets
    requestBody:
      required: true
      content:
        application/json:
          schema:
            type: object
            required:
              - ticket_type_id
              - quantity
              - visit_date
            properties:
              ticket_type_id:
                type: integer
                description: ID of the ticket type
              quantity:
                type: integer
                minimum: 1
                maximum: 10
              visit_date:
                type: string
                format: date
    responses:
      '201':
        description: Booking created successfully
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/BookingResponse'
```

## Pull Request Process

### Before Submitting

1. **Self Review**
   - [ ] Code follows project standards
   - [ ] Tests are written and passing
   - [ ] Documentation is updated
   - [ ] No sensitive data committed
   - [ ] Commit messages are clear

2. **Testing**
   ```bash
   # Run all tests
   vendor/bin/phpunit
   
   # Check code style
   vendor/bin/php-cs-fixer fix --dry-run
   
   # Static analysis
   vendor/bin/phpstan analyze
   ```

### PR Description Template

```markdown
## Description
Brief description of changes made.

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] Manual testing completed

## Screenshots (if applicable)
Add screenshots to help explain your changes.

## Checklist
- [ ] My code follows the project's style guidelines
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] New and existing unit tests pass locally with my changes
```

### Review Process

1. **Automated Checks**
   - Code style validation
   - Unit test execution
   - Security scanning
   - Performance analysis

2. **Human Review**
   - Code quality assessment
   - Architecture review
   - Security review
   - Documentation review

3. **Approval Requirements**
   - At least 2 approvals from core team
   - All automated checks pass
   - No unresolved discussions

## Issue Reporting

### Bug Reports

Use the bug report template:

```markdown
**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected behavior**
A clear and concise description of what you expected to happen.

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Environment:**
 - OS: [e.g. Windows 10]
 - Browser: [e.g. Chrome 91]
 - PHP Version: [e.g. 7.4.21]
 - Database: [e.g. MySQL 8.0]

**Additional context**
Add any other context about the problem here.
```

### Feature Requests

```markdown
**Is your feature request related to a problem?**
A clear and concise description of what the problem is.

**Describe the solution you'd like**
A clear and concise description of what you want to happen.

**Describe alternatives you've considered**
A clear and concise description of any alternative solutions or features you've considered.

**Additional context**
Add any other context or screenshots about the feature request here.
```

## Community

### Communication Channels

- **GitHub Discussions**: General discussions and Q&A
- **GitHub Issues**: Bug reports and feature requests
- **Email**: Technical questions at dev@zoo-management.com
- **Discord**: Real-time chat (coming soon)

### Getting Help

1. **Documentation**: Check existing docs first
2. **Search Issues**: Look for existing discussions
3. **Ask Questions**: Create a discussion thread
4. **Join Community**: Participate in code reviews

### Recognition

Contributors will be recognized in:
- GitHub contributors list
- CHANGELOG.md for significant contributions
- Documentation credits
- Annual contributor spotlight

---

**Thank you for contributing to Zoo Management System!** 🦁

Every contribution, no matter how small, helps make this project better for everyone.
