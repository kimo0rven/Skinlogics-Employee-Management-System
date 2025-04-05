<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messenger Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #000;
            font-family: Arial, sans-serif;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            display: flex;
            align-items: center;
            width: 80%;
            max-width: 1100px;
        }

        .text-section {
            width: 45%;
        }

        h1 {
            font-size: 2.5rem;
            color: #4385f4;
            margin-bottom: 10px;
        }

        p {
            font-size: 1rem;
            color: #ccc;
            margin-bottom: 20px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        input {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            background: #1c1c1e;
            border: 1px solid #333;
            color: #fff;
        }

        .login-button-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        button {
            padding: 10px 14px;
            background-color: #4385f4;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 1rem;
            cursor: pointer;
        }

        .forgot-password {
            color: #4385f4;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .alt-login {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .alt-login img {
            width: 150px;
            height: 40px;
            cursor: pointer;
            border-radius: 5px;
        }

        .mockup-section {
            width: 55%;
            display: flex;
            justify-content: center;
        }

        .mockup-section img {
            width: 100%;
            max-width: 450px;
            background-color: #121212;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                text-align: center;
            }

            .text-section, .mockup-section {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-section">
            <h1>A place for meaningful conversations</h1>
            <p>Messenger helps you connect with your Facebook friends and family, build your community, and deepen your interests.</p>
            <form class="login-form">
                <input type="text" placeholder="Email or phone number" required>
                <input type="password" placeholder="Password" required>
                <div class="login-button-container">
                    <button type="submit">Log in</button>
                    <a href="#" class="forgot-password">Forgotten your password?</a>
                </div>
            </form>
            <div class="alt-login">
                <img src="microsoft.jpg" alt="Get it from Microsoft">
                <img src="google.jpg" alt="Google Login">
            </div>
        </div>
        <div class="mockup-section">
            <img src="messenger.png" alt="Messenger Mockup">
        </div>
    </div>
</body>
</html>  