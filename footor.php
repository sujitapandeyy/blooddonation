<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .footer-link:hover {
            color: #f4e56f; /* Tailwind's yellow-400 */
        }
    </style>
</head>
<body>
    <footer class="bg-gray-800 text-gray-300">
        <div class="container mx-auto p-10 flex flex-col md:flex-row gap-10">
            <!-- About Us -->
            <div class="md:w-1/3 mb-8">
                <h2 class="text-2xl font-bold mb-4">About Us</h2>
                <p class="text-lg">
                    Our mission is to save lives by organizing blood donations and providing essential blood products to those in need. We strive to create a safe and efficient donation process and raise awareness about the importance of blood donation.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="md:w-1/3 mb-8">
                <h2 class="text-2xl font-bold mb-4">Quick Links</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li><a href="/index" class="footer-link">Home</a></li>
                    <li><a href="/index" class="footer-link">Donate Blood</a></li>
                    <li><a href="/index" class="footer-link">Find a Blood Bank</a></li>
                    <li><a href="/index" class="footer-link">Contact Us</a></li>
                    <li><a href="/index" class="footer-link">FAQs</a></li>
                </ul>
            </div>

            <!-- Get Involved -->
            <div class="md:w-1/3 mb-8">
                <h2 class="text-2xl font-bold mb-4">Get Involved</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li><a href="/volunteer" class="footer-link">Volunteer</a></li>
                    <li><a href="/organize-event" class="footer-link">Organize an Event</a></li>
                    <li><a href="/corporate-partnerships" class="footer-link">Corporate Partnerships</a></li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="md:w-1/3 mb-8">
                <h2 class="text-2xl font-bold mb-4 p-1">Contact Us</h2>
                <p><i class="fas fa-map-marker-alt p-1"></i> Machhapokhari, Kathmandu-16, Kathmandu, Kathmandu Metropolitan City, Kathmandu, Bagmati Province, 00971, Nepal</p>
                <p><i class="fas fa-phone p-1"></i>+977 10560892</p>
                <p><i class="fas fa-envelope p-1"></i> <a href="mailto:info@blooddonation.org" class="footer-link">info@blooddonation.org</a></p>
            </div>
        </div>

        <!-- Follow Us -->
        <div class="bg-gray-700 p-4 text-center">
            <h2 class="text-xl font-bold mb-4">Follow Us</h2>
            <div class="flex justify-center gap-4">
                <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" class="text-yellow-400 hover:text-yellow-500"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="text-yellow-400 hover:text-yellow-500"><i class="fab fa-instagram"></i></a>
                <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="text-yellow-400 hover:text-yellow-500"><i class="fab fa-twitter"></i></a>
                <a href="https://www.linkedin.com" target="_blank" rel="noopener noreferrer" class="text-yellow-400 hover:text-yellow-500"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="bg-gray-900 text-gray-50 text-center p-4">
            &copy; 2024 RaktaSewa. All rights reserved.
        </div>
    </footer>

    <!-- Include FontAwesome for icons -->
</body>
</html>
