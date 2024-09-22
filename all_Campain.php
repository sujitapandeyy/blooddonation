<?php
require('connection.php');

// Fetch all campaigns
$currentDate = date('Y-m-d');
$sql = "SELECT id, campaign_name, campaign_date FROM campaigns WHERE campaign_date >= ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('s', $currentDate);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .bg-img {
            background-image: url('img/type.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }
        .carousel {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
        }

        .carousel-item {
            flex: none;
            scroll-snap-align: center;
            width: calc(100% / 3); /* 3 items visible at a time */
            transition: transform 0.5s ease-in-out;
        }

        .carousel::-webkit-scrollbar {
            display: none;
        }

        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            padding: 10px;
            cursor: pointer;
            z-index: 10;
        }

        .arrow-left {
            left: 10px;
        }

        .arrow-right {
            right: 10px;
        }
    </style>
</head>

<body class="bg-gray-100">
    <section class="bg-red-100 w-full my-10 bg-img">
        <h2 class="text-4xl font-bold text-center mb-12 text-red-600">Available Campaigns</h2>
        <div class="relative max-w-6xl mx-auto">
            <div class="carousel max-w-4xl mx-auto  px-20 gap-10">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="carousel-item relative bg-white shadow-md rounded-lg overflow-hidden transform transition-transform duration-300 hover:scale-105">
                            <!-- Image for the campaign -->
                            <img src="img/slide1.png" alt="Campaign Image" class="w-full h-60 object-cover">
                            <div class="absolute inset-x-0 bottom-0 bg-white py-4 px-6 shadow-lg rounded-t-lg">
                                <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($row['campaign_name']); ?></h2>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($row['campaign_date']); ?></p>
                                <a href="campaign_detail.php?id=<?php echo $row['id']; ?>" class="inline-block mt-2 bg-red-500 hover:bg-red-400 text-white py-1 px-3 rounded-lg shadow-lg transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    View More
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center text-gray-600">No campaigns available at the moment.</p>
                <?php endif; ?>
            </div>
            <!-- Carousel navigation arrows -->
            <div class="arrow arrow-left" onclick="scrollCarousel(-1)">
                <i class="fas fa-chevron-left text-gray-800"></i>
            </div>
            <div class="arrow arrow-right" onclick="scrollCarousel(1)">
                <i class="fas fa-chevron-right text-gray-800"></i>
            </div>
        </div>
    </section>

    <script>
    const carousel = document.querySelector('.carousel');

    const scrollCarousel = (direction) => {
        const item = document.querySelector('.carousel-item');
        if (!item) return; // Prevents errors if there are no items

        const itemWidth = item.offsetWidth;

        carousel.scrollBy({
            left: itemWidth * direction,
            behavior: 'smooth'
        });

        // Check if the carousel has reached the end
        if (direction === 1 && carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth) {
            setTimeout(() => {
                carousel.scrollTo({ left: 0, behavior: 'smooth' });
            }, 500); // Delay for a smooth transition
        }
    };

    // Automatic scrolling every 5 seconds
    setInterval(() => {
        scrollCarousel(1); // Scroll to the right
    }, 5000);
</script>

</body>

</html>

<?php
$con->close();
?>


