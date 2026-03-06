<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4">

        <!-- Header -->
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
            IELTS Writing Test ✍️
        </h1>
        <p class="text-gray-600 mb-8">
            Simulate real IELTS exam conditions and get AI-based band score evaluation.
        </p>

        @if(session('error'))
            <div class="mb-6 bg-red-50 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('writing.start') }}" method="POST"
              class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 space-y-8">
            @csrf

            <!-- Test Type -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">
                    1️⃣ Choose Test Type
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="border rounded-xl p-4 cursor-pointer hover:border-indigo-600 transition">
                        <input type="radio" name="test_type" value="academic" class="hidden peer" required>
                        <div class="peer-checked:border-indigo-600 peer-checked:bg-indigo-50 border rounded-xl p-4">
                            <h3 class="font-bold text-lg text-gray-900">Academic</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                For university admission & professional registration
                            </p>
                        </div>
                    </label>

                    <label class="border rounded-xl p-4 cursor-pointer hover:border-purple-600 transition">
                        <input type="radio" name="test_type" value="general" class="hidden peer">
                        <div class="peer-checked:border-purple-600 peer-checked:bg-purple-50 border rounded-xl p-4">
                            <h3 class="font-bold text-lg text-gray-900">General Training</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                For migration, work & everyday English
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Task Selection -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">
                    2️⃣ Choose Writing Task
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="border rounded-xl p-4 cursor-pointer hover:border-gray-700 transition">
                        <input type="radio" name="task" value="task1" class="hidden peer" required>
                        <div class="peer-checked:bg-gray-100 border rounded-xl p-4">
                            <h3 class="font-bold text-lg text-gray-900">Task 1</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                ⏱ 20 minutes · 📝 Minimum 150 words
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                Academic: Graph / Chart / Diagram<br>
                                General: Letter (formal / informal)
                            </p>
                        </div>
                    </label>

                    <label class="border rounded-xl p-4 cursor-pointer hover:border-gray-700 transition">
                        <input type="radio" name="task" value="task2" class="hidden peer">
                        <div class="peer-checked:bg-gray-100 border rounded-xl p-4">
                            <h3 class="font-bold text-lg text-gray-900">Task 2</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                ⏱ 40 minutes · 📝 Minimum 250 words
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                Essay (opinion / discussion / problem)
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Exam Conditions -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <h3 class="font-semibold text-yellow-800 mb-2">
                    IELTS Exam Conditions
                </h3>
                <ul class="text-sm text-yellow-800 list-disc list-inside space-y-1">
                    <li>Timer starts immediately after you begin</li>
                    <li>No pause or restart during the test</li>
                    <li>Auto-save enabled</li>
                    <li>Spelling & grammar are evaluated strictly</li>
                </ul>
            </div>

            <!-- Start Button -->
            <button type="submit"
                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600
                       hover:from-indigo-700 hover:to-purple-700
                       text-white py-4 rounded-xl text-lg font-bold
                       transition-all shadow-md">
                Start Writing Test
            </button>

        </form>
    </div>
</x-app-layout>
