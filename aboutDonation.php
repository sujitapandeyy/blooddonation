<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blood Donation</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="container mx-auto p-4">
    <h1 class="text-4xl font-bold text-center mb-8">Learn About Donation</h1>
    
    <!-- Blood type selection -->
    <div class="flex justify-center space-x-4 mb-6">
      <button class="py-2 px-4 bg-gray-200 rounded hover:bg-gray-300" onclick="showCompatibility('A+')">A+</button>
      <button class="py-2 px-4 bg-gray-200 rounded hover:bg-gray-300" onclick="showCompatibility('B+')">B+</button>
      <button class="py-2 px-4 bg-gray-200 rounded hover:bg-gray-300" onclick="showCompatibility('AB+')">AB+</button>
      <button class="py-2 px-4 bg-red-500 text-white rounded" onclick="showCompatibility('O+')">O+</button>
      <button class="py-2 px-4 bg-gray-200 rounded hover:bg-gray-300" onclick="showCompatibility('A-')">A-</button>
      <button class="py-2 px-4 bg-gray-200 rounded hover:bg-gray-300" onclick="showCompatibility('O-')">O-</button>
      <button class="py-2 px-4 bg-gray-200 rounded hover:bg-gray-300" onclick="showCompatibility('B-')">B-</button>
      <button class="py-2 px-4 bg-gray-200 rounded hover:bg-gray-300" onclick="showCompatibility('AB-')">AB-</button>
    </div>

    <!-- Compatibility Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Can take from -->
      <div class="bg-yellow-100 p-4 rounded">
        <h2 class="text-xl font-bold">You can take from</h2>
        <p id="takeFrom" class="mt-2 text-lg"></p>
      </div>
      
      <!-- Can give to -->
      <div class="bg-blue-100 p-4 rounded">
        <h2 class="text-xl font-bold">You can give to</h2>
        <p id="giveTo" class="mt-2 text-lg"></p>
      </div>
    </div>

    <!-- Illustration -->
    <div class="mt-8 flex justify-center">
      <img src="img/slide1.png" alt="Blood Donation Illustration" class="w-100 h-100">
    </div>
    
    <p class="text-center mt-4 text-lg">One blood donation can save up to <span class="text-red-500 font-bold">Three Lives</span></p>
  </div>

  <!-- JavaScript for blood compatibility -->
  <script>
    // Compatibility data
    const compatibility = {
      'A+': { takeFrom: ['A+', 'A-', 'O+', 'O-'], giveTo: ['A+', 'AB+'] },
      'B+': { takeFrom: ['B+', 'B-', 'O+', 'O-'], giveTo: ['B+', 'AB+'] },
      'AB+': { takeFrom: ['A+', 'B+', 'AB+', 'O+', 'A-', 'B-', 'AB-', 'O-'], giveTo: ['AB+'] },
      'O+': { takeFrom: ['O+', 'O-'], giveTo: ['O+', 'A+', 'B+', 'AB+'] },
      'A-': { takeFrom: ['A-', 'O-'], giveTo: ['A+', 'A-', 'AB+', 'AB-'] },
      'O-': { takeFrom: ['O-'], giveTo: ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'] },
      'B-': { takeFrom: ['B-', 'O-'], giveTo: ['B+', 'B-', 'AB+', 'AB-'] },
      'AB-': { takeFrom: ['AB-', 'A-', 'B-', 'O-'], giveTo: ['AB+', 'AB-'] },
    };

    // Function to update the compatibility info
    function showCompatibility(bloodType) {
      const takeFrom = compatibility[bloodType].takeFrom.join(', ');
      const giveTo = compatibility[bloodType].giveTo.join(', ');

      document.getElementById('takeFrom').textContent = takeFrom;
      document.getElementById('giveTo').textContent = giveTo;
    }

    // Initialize with O+ data by default
    showCompatibility('O+');
  </script>
</body>
</html>





<h2 class="text-xl font-bold text-center p-4">Learn about Donation</h2>

                <div class="grid grid-cols-2 sm:grid-cols-8 gap-2 w-2/2">
                    <?php foreach ($bloodTypes as $bloodType): ?>
                        <button onclick="showCompatibility('<?= htmlspecialchars($bloodType) ?>')"
                            class="border-2 border-red-500 bg-white py-2 rounded hover:bg-red-500 hover:text-white transition duration-300">
                            <?= htmlspecialchars($bloodType) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="flex mb-10 mt-6 space-x-4">
                    <div class="flex-1 bg-red-100 p-4 rounded-lg text-center">
                        <h2 class="text-xl font-bold">Take from :</h2>
                        <p id="takeFrom" class="text-lg"></p>
                    </div>
                    <div class="flex-1 bg-blue-100 p-4 rounded text-center">
                        <h2 class="text-xl font-bold">Give to :</h2>
                        <p id="giveTo" class="text-lg"></p>
                    </div>
                </div>
            </div>
        </div>