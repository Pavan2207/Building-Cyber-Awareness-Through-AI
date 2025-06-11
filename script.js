document.addEventListener('DOMContentLoaded', () => {
    const chatWindow = document.getElementById('chat-window');
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');
    const recordButton = document.getElementById('record-button');
    const animationPrompt = document.getElementById('animation-prompt');
    const audienceSelect = document.getElementById('audience');
    const generateAnimationButton = document.getElementById('generate-animation-button');
    const animationStatus = document.getElementById('animation-status');
    const generatedVideo = document.getElementById('generated-video');
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;

    // --- Tab Switching Logic ---
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTabId = button.dataset.tab;

            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            button.classList.add('active');
            document.getElementById(targetTabId).classList.add('active');
        });
    });

    // --- Chatbot Functionality ---
    function addMessage(message, sender) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message', `${sender}-message`);
        messageElement.textContent = message;
        chatWindow.appendChild(messageElement);
        chatWindow.scrollTop = chatWindow.scrollHeight; // Auto-scroll to bottom
    }

    async function sendMessage() {
        const message = userInput.value.trim();
        if (message === '') return;

        addMessage(message, 'user');
        userInput.value = ''; // Clear input

        try {
            // Send message to PHP backend
            const chatResponse = await fetch('backend/chat_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: message }),
            });

            if (!chatResponse.ok) {
                const errorData = await chatResponse.json();
                throw new Error(errorData.error || `HTTP error! status: ${chatResponse.status}`);
            }

            const chatData = await chatResponse.json();
            const botResponseText = chatData.response;
            addMessage(botResponseText, 'bot');

            // Play voice response
            await playVoice(botResponseText);

        } catch (error) {
            console.error('Error sending message or playing voice:', error);
            addMessage(`Error: Could not get a response. (${error.message})`, 'bot');
        }
    }

    async function playVoice(text) {
        try {
            const voiceResponse = await fetch('backend/tts_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ text: text }),
            });

            if (!voiceResponse.ok) {
                const errorData = await voiceResponse.json();
                throw new Error(errorData.error || `HTTP error! status: ${voiceResponse.status}`);
            }

            const audioData = await voiceResponse.blob();
            const audioUrl = URL.createObjectURL(audioData);
            const audio = new Audio(audioUrl);
            audio.play();

            audio.onended = () => {
                URL.revokeObjectURL(audioUrl); // Clean up the URL
            };

        } catch (error) {
            console.error('Error playing voice:', error);
            // Optionally, add a message to the chat indicating voice error
            // addMessage(`(Voice playback failed: ${error.message})`, 'bot');
        }
    }

    sendButton.addEventListener('click', sendMessage);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // --- Voice Input Functionality ---
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        recordButton.addEventListener('click', async () => {
            if (!isRecording) {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    mediaRecorder.ondataavailable = (event) => {
                        audioChunks.push(event.data);
                    };
                    mediaRecorder.onstop = async () => {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                        audioChunks = []; // Clear for next recording

                        // In a real app, you'd send this blob to a speech-to-text API
                        // For simplicity, we'll just indicate recording stopped
                        console.log('Recording stopped. Audio blob created.');
                        // Here you could add a feature to send the audio to a server-side STT service
                        // For now, we'll just re-enable input and send text
                        userInput.value = "Voice input is not directly transcribed in this demo. Please type your message.";
                        // sendMessage(); // Or automatically send if you had STT
                    };

                    mediaRecorder.start();
                    isRecording = true;
                    recordButton.classList.add('recording');
                    recordButton.textContent = 'Recording...';
                    console.log('Recording started');

                } catch (err) {
                    console.error('Error accessing microphone:', err);
                    alert('Error accessing microphone. Please ensure permissions are granted.');
                }
            } else {
                mediaRecorder.stop();
                mediaRecorder.stream.getTracks().forEach(track => track.stop()); // Stop microphone access
                isRecording = false;
                recordButton.classList.remove('recording');
                recordButton.innerHTML = '<img src="microphone-icon.png" alt="Record">';
                console.log('Recording finished');
            }
        });
    } else {
        recordButton.style.display = 'none'; // Hide button if no microphone support
        console.warn('getUserMedia not supported on your browser!');
    }

    // --- AI Animation Functionality ---
    generateAnimationButton.addEventListener('click', async () => {
        const prompt = animationPrompt.value.trim();
        const audience = audienceSelect.value;

        if (!prompt) {
            alert('Please enter a description for the animation.');
            return;
        }

        animationStatus.textContent = 'Generating animation... This may take a few minutes.';
        animationStatus.classList.remove('error', 'success');
        animationStatus.classList.add('loading');
        generatedVideo.style.display = 'none';
        generatedVideo.src = ''; // Clear previous video

        try {
            const response = await fetch('backend/animation_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ prompt, audience }),
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.video_url) {
                generatedVideo.src = data.video_url;
                generatedVideo.style.display = 'block';
                animationStatus.textContent = 'Animation generated successfully!';
                animationStatus.classList.remove('loading', 'error');
                animationStatus.classList.add('success');
            } else {
                throw new Error('No video URL received from the API.');
            }

        } catch (error) {
            console.error('Error generating animation:', error);
            animationStatus.textContent = `Error: ${error.message}`;
            animationStatus.classList.remove('loading', 'success');
            animationStatus.classList.add('error');
            generatedVideo.style.display = 'none';
        }
    });
});