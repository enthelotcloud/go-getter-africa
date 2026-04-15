<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Go-Getter Brand Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white flex items-center justify-center h-screen">

    <div class="text-center px-6">

        <!-- Logo / Name -->
        <h1 class="text-4xl md:text-6xl font-bold mb-4">
            GO-GETTER BRAND AFRICA 🌍
        </h1>

        <p class="text-lg md:text-xl text-gray-300 mb-6">
            Official Website Launching Soon
        </p>

        <!-- Countdown -->
        <div id="countdown" class="text-3xl font-semibold mb-6"></div>

        <!-- Message -->
        <p class="text-gray-400 max-w-xl mx-auto mb-6">
            Empowering young men and women through leadership, talent support, and impactful community initiatives.
        </p>

        <!-- Contact -->
        <div class="text-sm text-gray-500">
            <p>Contact: 0727 976 566 / 0710 878 056</p>
            <p>Email: info@gogetterbrandafrica.co.ke</p>
        </div>

    </div>

    <script>
        // Set launch date (2 days from now)
        const launchDate = new Date();
        launchDate.setDate(launchDate.getDate() + 2);

        const countdown = document.getElementById("countdown");

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = launchDate - now;

            if (distance < 0) {
                countdown.innerHTML = "🚀 We Are Live!";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
            const minutes = Math.floor((distance / 1000 / 60) % 60);
            const seconds = Math.floor((distance / 1000) % 60);

            countdown.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        }

        setInterval(updateCountdown, 1000);
    </script>

</body>
</html>
