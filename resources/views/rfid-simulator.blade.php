<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RFID Card Simulator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes scan {
            0% { transform: translateY(-100%); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(100%); opacity: 0; }
        }
        .scanning::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, #3b82f6, transparent);
            animation: scan 2s ease-in-out infinite;
        }
        @keyframes pulse-success {
            0%, 100% { background-color: rgb(34, 197, 94); }
            50% { background-color: rgb(22, 163, 74); }
        }
        @keyframes pulse-error {
            0%, 100% { background-color: rgb(239, 68, 68); }
            50% { background-color: rgb(220, 38, 38); }
        }
        .success-pulse {
            animation: pulse-success 0.5s ease-in-out 2;
        }
        .error-pulse {
            animation: pulse-error 0.5s ease-in-out 2;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">🎫 RFID Card Simulator</h1>
                    <p class="text-gray-600">Scan RFID cards to check students in/out</p>
                </div>

                <!-- Scanner Display -->
                <div class="max-w-2xl mx-auto mb-8">
                    <div id="scanner" class="relative bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-8 shadow-xl overflow-hidden">
                        <div class="text-center text-white">
                            <svg class="w-24 h-24 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            <div id="scanner-status" class="text-2xl font-bold mb-2">Ready to Scan</div>
                            <div id="scanner-message" class="text-blue-100">Hold an RFID card near the reader</div>
                        </div>

                        <!-- Student Info Display -->
                        <div id="student-info" class="hidden mt-6 bg-white/10 backdrop-blur rounded-lg p-4">
                            <div class="text-white text-center">
                                <div class="text-3xl font-bold mb-2" id="student-name"></div>
                                <div class="text-lg mb-2" id="student-class"></div>
                                <div class="flex justify-center gap-4 text-sm">
                                    <div>
                                        <span class="text-blue-200">Check-in:</span>
                                        <span id="check-in-time" class="font-semibold">--:--</span>
                                    </div>
                                    <div>
                                        <span class="text-blue-200">Check-out:</span>
                                        <span id="check-out-time" class="font-semibold">--:--</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Input -->
                    <div class="mt-4">
                        <form id="manual-scan-form" class="flex gap-2">
                            <input 
                                type="text" 
                                id="card-input" 
                                placeholder="Enter RFID card number or click a card below..."
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                autofocus
                            >
                            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold">
                                Scan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- RFID Cards Grid -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Active RFID Cards</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($rfidCards as $card)
                        <div class="rfid-card bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg p-4 shadow-md cursor-pointer hover:shadow-xl transition-shadow"
                             data-card-number="{{ $card->card_number }}"
                             onclick="scanCard('{{ $card->card_number }}')">
                            <div class="flex items-start justify-between mb-2">
                                <div class="text-white">
                                    <div class="text-sm opacity-75">STUDENT ID CARD</div>
                                    <div class="text-2xl font-bold mt-1">{{ $card->user->name }}</div>
                                </div>
                                <svg class="w-8 h-8 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="text-white text-sm opacity-75 mb-1">
                                {{ $card->user->class->name ?? 'No Class Assigned' }}
                            </div>
                            <div class="text-white font-mono text-lg tracking-wider">
                                {{ $card->card_number }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Scans -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Recent Scans</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody id="recent-scans" class="bg-white divide-y divide-gray-200">
                            @foreach($recentScans as $scan)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $scan->student->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $scan->student->class->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $scan->check_in_time ? \Carbon\Carbon::parse($scan->check_in_time)->format('h:i A') : '--' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $scan->check_out_time ? \Carbon\Carbon::parse($scan->check_out_time)->format('h:i A') : '--' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $scan->status === 'present' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($scan->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const scanner = document.getElementById('scanner');
        const scannerStatus = document.getElementById('scanner-status');
        const scannerMessage = document.getElementById('scanner-message');
        const studentInfo = document.getElementById('student-info');
        const cardInput = document.getElementById('card-input');

        function scanCard(cardNumber) {
            cardInput.value = cardNumber;
            performScan(cardNumber);
        }

        document.getElementById('manual-scan-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const cardNumber = cardInput.value.trim();
            if (cardNumber) {
                performScan(cardNumber);
            }
        });

        function performScan(cardNumber) {
            // Add scanning animation
            scanner.classList.add('scanning');
            scannerStatus.textContent = 'Scanning...';
            scannerMessage.textContent = 'Processing card number: ' + cardNumber;

            fetch('/rfid/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ card_number: cardNumber })
            })
            .then(response => response.json())
            .then(data => {
                scanner.classList.remove('scanning');
                
                if (data.success) {
                    // Success
                    scanner.classList.add('success-pulse');
                    scannerStatus.textContent = data.action === 'check-in' ? '✓ Checked In' : '✓ Checked Out';
                    scannerMessage.textContent = data.message;
                    
                    // Show student info
                    document.getElementById('student-name').textContent = data.student.name;
                    document.getElementById('student-class').textContent = data.student.class;
                    document.getElementById('check-in-time').textContent = data.student.check_in || '--:--';
                    document.getElementById('check-out-time').textContent = data.student.check_out || '--:--';
                    studentInfo.classList.remove('hidden');
                    
                    // Play success sound (optional)
                    playSound('success');
                    
                    setTimeout(() => {
                        scanner.classList.remove('success-pulse');
                        resetScanner();
                    }, 3000);
                } else {
                    // Error
                    scanner.classList.add('error-pulse');
                    scannerStatus.textContent = '✗ Error';
                    scannerMessage.textContent = data.message;
                    
                    if (data.student) {
                        document.getElementById('student-name').textContent = data.student.name;
                        document.getElementById('student-class').textContent = data.student.class;
                        document.getElementById('check-in-time').textContent = data.student.check_in || '--:--';
                        document.getElementById('check-out-time').textContent = data.student.check_out || '--:--';
                        studentInfo.classList.remove('hidden');
                    }
                    
                    playSound('error');
                    
                    setTimeout(() => {
                        scanner.classList.remove('error-pulse');
                        resetScanner();
                    }, 3000);
                }
                
                // Clear input
                cardInput.value = '';
                cardInput.focus();
                
                // Reload recent scans (optional, you could update via AJAX)
                setTimeout(() => location.reload(), 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                scanner.classList.remove('scanning');
                scanner.classList.add('error-pulse');
                scannerStatus.textContent = '✗ Connection Error';
                scannerMessage.textContent = 'Failed to connect to server';
                
                setTimeout(() => {
                    scanner.classList.remove('error-pulse');
                    resetScanner();
                }, 3000);
            });
        }

        function resetScanner() {
            scannerStatus.textContent = 'Ready to Scan';
            scannerMessage.textContent = 'Hold an RFID card near the reader';
            studentInfo.classList.add('hidden');
        }

        function playSound(type) {
            // You can add actual sound files here
            // For now, this is just a placeholder
            if (type === 'success') {
                // Play success beep
                console.log('🔊 Beep! (success)');
            } else if (type === 'error') {
                // Play error beep
                console.log('🔊 Buzz! (error)');
            }
        }

        // Auto-focus on input
        cardInput.focus();
    </script>
</body>
</html>
