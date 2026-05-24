<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizify - AI Study Buddy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen">
    
    <nav class="bg-blue-600 text-white p-4 shadow-md">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold tracking-tight">Quizify.</h1>
            <span class="text-sm bg-blue-700 px-3 py-1 rounded-full">AI Study Buddy</span>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto p-4 py-8 grid md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 h-fit">
            <h2 class="text-lg font-bold mb-2">1. Masukkan Materi Kuliah</h2>
            <p class="text-sm text-gray-500 mb-4">Copy-paste modul atau slide presentasi di sini.</p>
            <textarea id="materiInput" rows="12" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none resize-none mb-4" placeholder="Contoh: Kecerdasan Buatan (AI) adalah simulasi proses kecerdasan manusia oleh mesin..."></textarea>
            <button id="generateBtn" onclick="generateBelajar()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-colors flex justify-center items-center gap-2">
                <span id="btnText">Buat Rangkuman & Kuis</span>
            </button>
            <p id="errorMsg" class="text-red-500 text-sm mt-3 hidden"></p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold mb-4">2. Hasil Belajar</h2>
            
            <div id="loading" class="hidden flex-col items-center justify-center h-64 text-gray-400">
                <svg class="animate-spin h-8 w-8 mb-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <p>AI sedang membaca & menyusun kuis...</p>
            </div>

            <div id="emptyState" class="flex flex-col items-center justify-center h-64 text-gray-400 text-center">
                <p>Belum ada materi diproses.<br>Hasil rangkuman dan kuis akan muncul di sini.</p>
            </div>

            <div id="resultContainer" class="hidden">
                <div class="mb-6">
                    <h3 class="font-bold text-blue-700 mb-2 border-b pb-1">Rangkuman Kunci</h3>
                    <ul id="rangkumanList" class="list-disc pl-5 space-y-1 text-sm text-gray-700"></ul>
                </div>

                <div>
                    <h3 class="font-bold text-blue-700 mb-2 border-b pb-1">Uji Pemahaman</h3>
                    <div id="kuisList" class="space-y-4"></div>
                </div>
            </div>
        </div>
    </main>

    <script>
        async function generateBelajar() {
            const materi = document.getElementById('materiInput').value;
            const btn = document.getElementById('generateBtn');
            const btnText = document.getElementById('btnText');
            const errorMsg = document.getElementById('errorMsg');
            const loading = document.getElementById('loading');
            const emptyState = document.getElementById('emptyState');
            const resultContainer = document.getElementById('resultContainer');

            if (materi.length < 50) {
                errorMsg.innerText = "Materi terlalu pendek. Masukkan teks yang lebih panjang.";
                errorMsg.classList.remove('hidden');
                return;
            }

            // Reset UI
            errorMsg.classList.add('hidden');
            emptyState.classList.add('hidden');
            resultContainer.classList.add('hidden');
            loading.classList.remove('hidden');
            btn.disabled = true;
            btnText.innerText = "Memproses...";

            try {
                const response = await fetch('/api/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ materi })
                });

                const res = await response.json();

                if (res.success) {
                    renderResult(res.data);
                } else {
                    throw new Error(res.message);
                }
            } catch (error) {
                errorMsg.innerText = "Terjadi kesalahan. Pastikan API Key valid dan koneksi internet stabil.";
                errorMsg.classList.remove('hidden');
                emptyState.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
                btn.disabled = false;
                btnText.innerText = "Buat Rangkuman & Kuis";
            }
        }

        function renderResult(data) {
            const rangkumanList = document.getElementById('rangkumanList');
            const kuisList = document.getElementById('kuisList');
            
            rangkumanList.innerHTML = '';
            kuisList.innerHTML = '';

            // Render Rangkuman
            data.rangkuman.forEach(poin => {
                const li = document.createElement('li');
                li.innerText = poin;
                rangkumanList.appendChild(li);
            });

            // Render Kuis
            data.kuis.forEach((item, index) => {
                let opsiHTML = '';
                for (const [key, value] of Object.entries(item.opsi)) {
                    opsiHTML += `
                        <button onclick="cekJawaban(this, '${key}', '${item.jawaban_benar}', '${item.penjelasan}')" 
                                class="w-full text-left p-2 text-sm border rounded hover:bg-gray-50 transition">
                            <span class="font-bold mr-2">${key}.</span> ${value}
                        </button>
                    `;
                }

                const kuisHTML = `
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <p class="font-semibold text-sm mb-3">${index + 1}. ${item.soal}</p>
                        <div class="space-y-2">${opsiHTML}</div>
                        <div class="mt-3 text-sm p-3 rounded hidden response-box"></div>
                    </div>
                `;
                kuisList.innerHTML += kuisHTML;
            });

            document.getElementById('resultContainer').classList.remove('hidden');
        }

        function cekJawaban(btn, jawabanUser, jawabanBenar, penjelasan) {
            const container = btn.parentElement.parentElement;
            const responseBox = container.querySelector('.response-box');
            
            const buttons = container.querySelectorAll('button');
            buttons.forEach(b => b.disabled = true);

            if (jawabanUser === jawabanBenar) {
                btn.classList.add('bg-green-100', 'border-green-500');
                responseBox.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-300');
                responseBox.innerHTML = `<strong>Benar! 🎉</strong> ${penjelasan}`;
            } else {
                btn.classList.add('bg-red-100', 'border-red-500');
                responseBox.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-300');
                responseBox.innerHTML = `<strong>Kurang Tepat.</strong> Jawaban yang benar adalah ${jawabanBenar}. ${penjelasan}`;
            }
            
            responseBox.classList.remove('hidden');
        }
    </script>
</body>
</html>