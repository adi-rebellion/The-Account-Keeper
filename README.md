# The Account Keeper

## Description

This project involves building a Laravel-based system to manage and analyze financial transactions for users.


## Features

- User Registration & Login with token-based authentication
- An API to handle the creation of credit and debit transactions for users, with real-time balance calculation.
- API's to get required transactional details.

## Requirements

- Laravel 10.10
- Docker for deployment (`sudo docker compose build` and `sudo docker compose up`)
- PHP runs on port 39100, PHPMyAdmin on port 29182

## Installation and Setup

1. Clone the repository:

   ```bash
   git clone https://github.com/adi-rebellion/The-Account-Keeper.git
   cd TaskKeeper
   ```

2. Build and start Docker containers:

   ```bash
   sudo docker compose build
   sudo docker compose up
   ```

3. Access the application in your web browser:

   ```
   http://localhost:39100
   ```






## API Documentation

📘 API documentation is available in Swagger format at:

Explore the API using Swagger UI with Bearer authorization.

1. Click on the `Authorize` button.
2. Enter `Bearer your_access_token` in the `Value` field.
3. Click `Authorize`.
4. Now you can explore and test API endpoints securely.


```
http://localhost:39100/api/documentation
```

📘 API documentation is available in Postman format at:

 
1. Click on the `Import` button.
2. Enter `URL` in the `Value` field.
3. Click `Import`.
4. Now you can explore and test API endpoints securely.


```
https://documenter.getpostman.com/view/13214997/2sA3s4nWCp
```



### Authentication

#### User Register
- Endpoint: `/api/register`
- Method: POST
- Parameters:
  - `name` (string): User's name
  - `email` (string): User's email address
  - `password` (string): User's password
  - `confirm_password` (string): User's confirm password
- Response:
  - `message`: User registered successfully. 
  - `access_token`: Token for accessing protected endpoints

#### User Login
- Endpoint: `/api/login`
- Method: POST
- Parameters:
  - `email` (string): User's email address
  - `password` (string): User's password
- Response:
  - `message`: User logged in successfully. 
  - `access_token`: Token for accessing protected endpoints

#### User Logout
- Endpoint: `/api/logout`
- Method: POST
- Headers:
  - `Authorization: Bearer {access_token}`
- Response:
  - `message`: User logged out successfully. 


## Contributors

- Aditya Naidu

