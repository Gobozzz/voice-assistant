document.addEventListener('DOMContentLoaded', function () {
    const USER_ROLE = 'user'
    const ASSISTANT_ROLE = 'assistant'
    const ASSISTANT_LOGO_PATH = "voice-assistant/images/logo.jpg"
    const MIN_LENGTH_MESSAGE = 3
    const API_ROUTE_URL = '/voice-assistant-site'
    const KEY_MESSAGES_LOCAL_STORAGE = "messages-voice-assistant-site"
    const COUNT_SAVED_MESSAGES = 30

    let messages = getSavedMessagesInMemory()
    let audioAnswer = null
    let mediaRecorder = null

    const fixedButton = document.querySelector('.voice-assistant-by-site-button')
    const chatNode = document.querySelector('.voice-assistant-by-site-chat')
    const messagesNode = document.querySelector('.voice-assistant-by-site-chat-messages')
    const chatFlowClose = document.querySelector('.voice-assistant-by-site-chat-close-flow')
    const chatCloseButton = document.querySelector('.voice-assistant-by-site-chat-close')
    const chatClearButton = document.querySelector('.voice-assistant-by-site-chat-clear')
    const chatInput = document.querySelector('.voice-assistant-by-site-chat-bottom-input')
    const chatSendButton = document.querySelector('.voice-assistant-by-site-controls-send-button')
    const chatAudioButton = document.querySelector('.voice-assistant-by-site-controls-audio-button')
    const chatLoader = document.querySelector('.voice-assistant-by-site-chat-loader')
    const chatErrors = document.querySelector('.voice-assistant-by-site-chat-errors')

    fixedButton.style.display = "block"

    fixedButton.onclick = openChat

    chatFlowClose.onclick = closeChat

    chatCloseButton.onclick = closeChat

    chatSendButton.onclick = textMessageSendHandler

    chatClearButton.onclick = clearChat

    chatAudioButton.onclick = () => {
        stopCurrentAudioAnswer()
        chatAudioButton.classList.toggle('recording')
        if (chatAudioButton.classList.contains('recording')) {
            recordAudio()
        } else {
            mediaRecorder.stop()
        }
    }

    if (messages.length > 0) {
        rerenderMessages()
    }

    function setError(text) {
        const error = document.createElement('div')
        error.className = "voice-assistant-by-site-chat-error"
        error.textContent = text

        chatErrors.insertAdjacentElement('afterbegin', error)

        setTimeout(() => error.remove(), 3000)
    }

    async function recordAudio() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({audio: true});

            mediaRecorder = new MediaRecorder(stream);
            const audioChunks = [];

            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data);
            };

            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, {type: 'audio/webm'});

                sendQuestionOnServer({audioBlob})

                stream.getTracks().forEach(track => track.stop());
            };

            mediaRecorder.start();

            return {
                stop: () => mediaRecorder.stop(),
                pause: () => mediaRecorder.pause(),
                resume: () => mediaRecorder.resume(),
                state: () => mediaRecorder.state
            };

        } catch (error) {
            chatAudioButton.classList.remove('recording')
            alert('Ошибка доступа к микрофону');
            throw error;
        }
    }

    function textMessageSendHandler() {
        const question = chatInput.value.trim()
        if (question.length < MIN_LENGTH_MESSAGE) {
            setError(`Минимальная длина текстового сообщения ${MIN_LENGTH_MESSAGE} симв.`)
            return
        }
        chatInput.value = ""
        sendQuestionOnServer({text: question})
    }

    async function sendQuestionOnServer({text = null, audioBlob = null}) {
        disabledControlTools()
        loaderOn()
        const data = new FormData()
        if (audioBlob) {
            data.append('audio', audioBlob, 'recording.webm');
        } else if (text) {
            data.append('text_question', text)
        }
        const lastMessages = getLastMessages()
        lastMessages.forEach((message, index) => {
            data.append(`messages[${index}][content]`, message.content);
            data.append(`messages[${index}][role]`, message.role);
        });
        const response = await fetch(API_ROUTE_URL, {
            method: 'POST',
            body: data,
            headers: {
                "Accept": "application/json"
            }
        })

        stopCurrentAudioAnswer()

        if (response.ok) {
            const data = await response.json()
            messages.push(
                {content: data.question, role: USER_ROLE},
                {content: data.answer, role: ASSISTANT_ROLE},
            )
            if (data.voice) {
                playAudioAnswerAssistant(data.voice)
            }
            rerenderMessages()
            scrollToLastUserMessage()
            saveMessagesMemory()
        } else {
            setError("Не удалось получить ответ от ассистента")
        }
        undisabledControlTools()
        loaderOff()
    }

    function stopCurrentAudioAnswer() {
        if (audioAnswer) {
            audioAnswer.pause()
        }
    }

    function resumeCurrentAudioAnswer() {
        if (audioAnswer && audioAnswer.currentTime < audioAnswer.duration) {
            audioAnswer.play()
        }
    }

    function playAudioAnswerAssistant(url) {
        audioAnswer = new Audio(url);
        audioAnswer.volume = 1
        audioAnswer.currentTime = 0
        audioAnswer.play()
    }

    function clearChat() {
        messages = []
        saveMessagesMemory()
        rerenderMessages()
        stopCurrentAudioAnswer()
    }

    function getSavedMessagesInMemory() {
        const messagesSaved = window.localStorage.getItem(KEY_MESSAGES_LOCAL_STORAGE)
        if (messagesSaved === null) return []
        const messagesParsed = JSON.parse(messagesSaved)
        if (Array.isArray(messagesParsed)) {
            return messagesParsed.slice(-COUNT_SAVED_MESSAGES)
        }
        return []
    }

    function saveMessagesMemory() {
        const lastMessages = getLastMessages()
        window.localStorage.setItem(KEY_MESSAGES_LOCAL_STORAGE, JSON.stringify(lastMessages))
    }

    function getLastMessages() {
        return messages.slice(-COUNT_SAVED_MESSAGES)
    }

    function scrollToLastUserMessage() {
        const allMessages = document.querySelectorAll('.voice-assistant-by-site-chat-message.user')
        if (allMessages.length === 0) return
        const lastMessage = allMessages[allMessages.length - 1]
        lastMessage.scrollIntoView({behavior: 'smooth'});
    }

    function loaderOn() {
        chatLoader.classList.add('active')
    }

    function loaderOff() {
        chatLoader.classList.remove('active')
    }

    function disabledControlTools() {
        chatInput.disabled = true
        chatSendButton.disabled = true
        chatAudioButton.disabled = true
    }

    function undisabledControlTools() {
        chatInput.disabled = false
        chatSendButton.disabled = false
        chatAudioButton.disabled = false
    }

    function openChat() {
        document.body.style.overflow = "hidden"
        chatNode.classList.add('active')
        chatFlowClose.classList.add('active')
        scrollToLastUserMessage()
        resumeCurrentAudioAnswer()
    }

    function closeChat() {
        document.body.style.overflow = "visible"
        chatNode.classList.remove('active')
        chatFlowClose.classList.remove('active')
        stopCurrentAudioAnswer()
    }

    function rerenderMessages() {
        messagesNode.innerHTML = ""
        messages.forEach(message => {
            const messageNode = createMessageNode(message)
            messagesNode.appendChild(messageNode)
        })
    }

    function createMessageNode(data) {
        const message = document.createElement('div')
        message.className = `voice-assistant-by-site-chat-message ${data.role === USER_ROLE ? 'user' : ''}`

        let avatarMessageAuthor = null;
        if (data.role === ASSISTANT_ROLE) {
            avatarMessageAuthor = document.createElement('img')
            avatarMessageAuthor.src = ASSISTANT_LOGO_PATH
            avatarMessageAuthor.className = "voice-assistant-by-site-chat-message-icon"
            avatarMessageAuthor.alt = "Логотип нейро-ассистента сайта"
        }

        const contentMessage = document.createElement('div')
        contentMessage.className = "voice-assistant-by-site-chat-message-content"
        contentMessage.innerHTML = marked.parse(data.content)

        if (avatarMessageAuthor) {
            message.appendChild(avatarMessageAuthor)
        }

        message.appendChild(contentMessage)

        return message
    }

})
