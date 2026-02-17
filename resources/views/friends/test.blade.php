<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-6">Friends Test Page</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Search for Friends</h2>

            <!-- Search Input -->
            <div class="mb-4">
                <input type="text"
                       id="friend-search"
                       placeholder="Search for users by name or email..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Search Results -->
            <div id="search-results" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium mb-2">Search Results:</h3>
                <div id="results-list"></div>
            </div>

            <!-- Test Buttons -->
            <div class="mt-6 space-x-4">
                <button onclick="testSearch()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Test Search
                </button>
                <button onclick="testSendRequest()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Test Send Request
                </button>
            </div>

            <!-- Debug Info -->
            <div id="debug-info" class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                <h3 class="font-medium text-yellow-800">Debug Info:</h3>
                <div id="debug-content"></div>
            </div>
        </div>
    </div>

    <script>
        function testSearch() {
            const query = document.getElementById('friend-search').value || 'test';
            const debugDiv = document.getElementById('debug-content');

            debugDiv.innerHTML = 'Testing search with query: ' + query;

            fetch(`/friends/search?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                debugDiv.innerHTML += '<br>Response status: ' + response.status;
                return response.json();
            })
            .then(data => {
                debugDiv.innerHTML += '<br>Response data: ' + JSON.stringify(data, null, 2);

                const resultsDiv = document.getElementById('search-results');
                const resultsList = document.getElementById('results-list');

                if (data.length > 0) {
                    resultsList.innerHTML = data.map(user => `
                        <div class="p-2 border-b border-gray-200">
                            <strong>${user.name}</strong> - ${user.email} (Status: ${user.friendship_status})
                        </div>
                    `).join('');
                    resultsDiv.classList.remove('hidden');
                } else {
                    resultsList.innerHTML = '<div class="text-gray-500">No users found</div>';
                    resultsDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                debugDiv.innerHTML += '<br>Error: ' + error.message;
                console.error('Search error:', error);
            });
        }

        function testSendRequest() {
            const debugDiv = document.getElementById('debug-content');
            debugDiv.innerHTML = 'Testing send friend request...';

            // Get first user ID from search results or use a test ID
            const testUserId = 2; // Assuming user ID 2 exists

            fetch('/friends/send-request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ friend_id: testUserId })
            })
            .then(response => {
                debugDiv.innerHTML += '<br>Send request response status: ' + response.status;
                return response.json();
            })
            .then(data => {
                debugDiv.innerHTML += '<br>Send request response: ' + JSON.stringify(data, null, 2);
            })
            .catch(error => {
                debugDiv.innerHTML += '<br>Send request error: ' + error.message;
                console.error('Send request error:', error);
            });
        }

        // Test search on page load
        document.addEventListener('DOMContentLoaded', function() {
            testSearch();
        });
    </script>
</body>
</html>
