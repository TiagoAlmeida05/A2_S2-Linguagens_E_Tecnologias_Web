# Freelance Marketplace Platform

A web-based platform that facilitates the offering and hiring of freelance services. The platform provides a seamless experience for users to easily list, browse, and hire services.

## Features

- User authentication (register, login, logout)
- User roles (client, freelancer, admin)
- Service management (create, browse, view, purchase)
- Category management
- Messaging system
- Review and rating system
- Admin dashboard
- Responsive design
- Live search functionality

## Technologies Used

- PHP 7.4+
- SQLite 3
- HTML5
- CSS3
- JavaScript (ES6+)
- AJAX

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd <repository-name>
```

2. Start the PHP development server:
```bash
php -S localhost:9000
```

3. Access the application at `http://localhost:9000`

## Database Structure

The application uses SQLite with the following tables:

- `Users`: Stores user information
- `Categories`: Stores service categories
- `Services`: Stores service listings
- `Transactions`: Stores service purchases
- `Messages`: Stores user messages
- `Reviews`: Stores service reviews

## Security Features

- Password hashing using PHP's password_hash()
- CSRF protection
- SQL injection prevention using prepared statements
- XSS prevention using htmlspecialchars()
- Input sanitization
- Secure session handling

## File Structure

```
public/
├── api/
│   └── search.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── includes/
│   ├── config.php
│   ├── db.php
│   └── functions.php
├── pages/
│   ├── admin/
│   │   └── dashboard.php
│   ├── messages/
│   │   ├── inbox.php
│   │   └── conversation.php
│   ├── services/
│   │   ├── list.php
│   │   ├── create.php
│   │   └── view.php
│   ├── home.php
│   ├── login.php
│   ├── register.php
│   ├── profile.php
│   └── logout.php
└── templates/
    ├── header.php
    └── footer.php
```

## Usage

1. Register a new account as either a client or freelancer
2. Log in to your account
3. Browse services or create your own services
4. Communicate with other users through the messaging system
5. Purchase services or receive payments
6. Leave reviews for completed services

## Admin Features

- Manage user roles
- Create and manage categories
- Monitor services and transactions
- Access user information

## Contributing

1. Fork the repository
2. Create a new branch
3. Make your changes
4. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Authors

- Your Name

## Acknowledgments

- Web Languages and Technologies course
- All contributors and users of the platform 