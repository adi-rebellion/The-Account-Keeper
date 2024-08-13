<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .raleway-font {
            font-family: "Raleway", sans-serif;
        }
        .theme_bg {
            background-color: #00b4d8;
            color: white;
        }
        .theme_fg {
            color: #00b4d8;
            background-color: white;
        }
        .half-bg {
            position: relative;
            top: -100px;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-transparent fixed w-full z-10">
        <div class="container mx-auto flex flex-wrap items-center justify-between py-4 px-4">
            <div class="flex items-center lg:hidden">
                <button id="navbarToggle" class="text-white focus:outline-none">
                    <i class="fas fa-bars fa-lg"></i>
                </button>
            </div>
            <div class="hidden lg:flex flex-grow items-center">
                <div class="text-md flex-grow">
                    <a href="#home" class="block mt-4 lg:inline-block lg:mt-0 text-white hover:text-gray-200 mr-4 raleway-font font-bold">Home</a>
                    <a href="#products" class="block mt-4 lg:inline-block lg:mt-0 text-white hover:text-gray-200 mr-4 raleway-font font-bold">Products</a>
                    <a href="#pricing" class="block mt-4 lg:inline-block lg:mt-0 text-white hover:text-gray-200 mr-4 raleway-font font-bold">Pricing</a>
                    <a href="#contact" class="block mt-4 lg:inline-block lg:mt-0 text-white hover:text-gray-200 raleway-font font-bold">Contact</a>
                </div>
                <div class="mt-4 lg:mt-0">
                    <a href="#login" class="inline-block text-md px-4 py-2 text-white hover:text-gray-200 raleway-font font-bold">Login</a>
                    <a href="#become-member" class="inline-block text-sm px-4 py-4 theme_bg raleway-font font-bold">
                        Become a Member <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div id="mobileNav" class="lg:hidden hidden flex flex-col px-4 mt-2">
            <a href="#home" class="block text-white raleway-font font-bold py-2">Home</a>
            <a href="#products" class="block text-white raleway-font font-bold py-2">Products</a>
            <a href="#pricing" class="block text-white raleway-font font-bold py-2">Pricing</a>
            <a href="#contact" class="block text-white raleway-font font-bold py-2">Contact</a>
            <a href="#login" class="block text-white raleway-font font-bold py-2">Login</a>
            <a href="#become-member" class="block text-white raleway-font font-bold py-2">Become a Member</a>
        </div>
    </nav>

    <!-- Landing Section -->
    <section class="relative h-screen flex flex-col justify-center items-center bg-cover bg-center px-4" style="background-image: url('https://images.pexels.com/photos/25589787/pexels-photo-25589787/free-photo-of-laptop-with-blank-screen.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');">
        <div class="absolute inset-0 bg-black opacity-50"></div>
        <div class="relative text-center text-white z-10">
            <h1 class="text-4xl lg:text-7xl font-bold raleway-font">Creating a Beautiful <br>& Useful Solutions</h1>
            <p class="mt-4 text-lg">We know how large objects will act, but things on a <br> small scale just do not act that way.</p>
            <div class="mt-8 ">
                <a href="#get-started" class="btn theme_bg rounded-full text-white py-3 px-6 mb-4 lg:mb-0 lg:mr-4">Get Quote Now</a>
                <a href="#get-started" class="btn border-2 border-white rounded-full text-white py-3 px-6">Learn More</a>
            </div>
        </div>
    </section>


    <section class="relative w-full flex justify-center items-center half-bg">
        <div class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-3 gap-8 px-6 md:px-12">
            <!-- Card 1 -->
            <div class="bg-white p-6 shadow-lg rounded-lg text-left">
                <i class="fas fa-users fa-3x theme_fg mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Innovative Solutions</h3>
                <p class="text-gray-600">The quick fox jumps over the lazy dog.</p>
            </div>
            <!-- Card 2 -->
            <div class="bg-white p-6 shadow-lg rounded-lg text-left">
                <i class="fas fa-users fa-3x theme_fg mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Raising Funds</h3>
                <p class="text-gray-600">The quick fox jumps over the lazy dog.</p>
            </div>
            <!-- Card 3 -->
            <div class="bg-white p-6 shadow-lg rounded-lg text-left">
                <i class="fas fa-users fa-3x theme_fg mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Finincial Analysis</h3>
                <p class="text-gray-600">The quick fox jumps over the lazy dog.</p>
            </div>
        </div>
    </section>




    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        document.getElementById('navbarToggle').addEventListener('click', function() {
            document.getElementById('mobileNav').classList.toggle('hidden');
        });
    </script>
</body>
</html>
