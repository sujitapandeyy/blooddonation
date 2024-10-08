<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Continuous Loop Carousel</title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/72f30a4d56.js" crossorigin="anonymous"></script>
    <!-- Favicon -->
    <link rel="icon" href="favIcon.png" type="image/png">
    <style>
        .carousel-wrapper {
            display: flex;
            transition: transform 1.5s ease-in-out;
        }

        .carousel-item {
            min-width: 100%;
            box-sizing: border-box;
        }

        .carousel-controls {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }

        /* Custom height for carousel */
        .carousel-container {
            height: 500px; /* Customize the height as needed */
        }

        .carousel-item img {
            height: 55%; /* Ensure images fill the carousel container */
            object-fit: cover; /* Cover ensures the image covers the container without distortion */
        }
    </style>
</head>
<body>
    <div class="max-full mt-32 relative overflow-hidden px-1 carousel-container">
        <div class="carousel flex relative">
            <div class="carousel-wrapper">
                <!-- Duplicate images for seamless loop -->
                <div class="carousel-item rounded">
                    <img src="./img/slide1.png" alt="Slide 1" class="w-full rounded">
                </div>
                <div class="carousel-item rounded">
                    <img src="./img/land2.png" alt="Slide 2" class="w-full rounded">
                </div>
                <div class="carousel-item rounded">
                    <img src="./img/slide4.png" alt="Slide 4" class="w-full rounded">
                </div>
                <div class="carousel-item rounded">
                    <img src="./img/slide1.png" alt="Slide 1" class="w-full rounded">
                </div>
                <div class="carousel-item rounded">
                    <img src="./img/land2.png" alt="Slide 2" class="w-full rounded">
                </div>
                <div class="carousel-item rounded">
                    <img src="./img/slide4.png" alt="Slide 4" class="w-full rounded">
                </div>
            </div>
            <div class="carousel-controls">
                <button id="prevButton" class="bg-gray-200 p-1 text-top rounded mb-80 hover:bg-gray-600">
                    <i class="fas fa-chevron-left font-bold text-white text-lg "></i>
                </button>
                <button id="nextButton" class="bg-gray-200 p-1 mb-80 rounded hover:bg-gray-600 ">
                    <i class="fas fa-chevron-right font-bold text-white text-lg "></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentIndex = 0;
        const items = document.querySelectorAll('.carousel-item');
        const totalItems = items.length;
        const carouselWrapper = document.querySelector('.carousel-wrapper');
        const itemWidth = items[0].clientWidth;

        function updateCarousel() {
            carouselWrapper.style.transform = `translateX(${-currentIndex * itemWidth}px)`;
        }

        function goToNext() {
            currentIndex++;
            if (currentIndex >= totalItems / 2) {
                currentIndex = 0; // Reset to the first image
                carouselWrapper.style.transition = 'none'; // Disable transition for a seamless loop
                carouselWrapper.style.transform = `translateX(0px)`; // Immediately jump to the start
                // Force a reflow to reset the transition
                carouselWrapper.offsetHeight; 
                carouselWrapper.style.transition = 'transform 1.5s ease-in-out'; // Re-enable transition
            }
            updateCarousel();
        }

        document.getElementById('nextButton').addEventListener('click', goToNext);

        setInterval(goToNext, 4000); // Automatically change slide every 4 seconds
    </script>
</body>
</html>
