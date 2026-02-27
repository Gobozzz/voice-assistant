<link rel="stylesheet" href="{{asset('/voice-assistant/css/site-assistant.css')}}">

<button class="voice-assistant-by-site-button">
    <img src="{{asset('/voice-assistant/images/logo.jpg')}}" alt="Логотип нейро-ассистента сайта">
</button>

<div class="voice-assistant-by-site-chat-close-flow"></div>

<div class="voice-assistant-by-site-chat">
    <div class="voice-assistant-by-site-chat-errors"></div>
    <div class="voice-assistant-by-site-chat-loader">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
             class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
        </svg>
    </div>
    <div class="voice-assistant-by-site-chat-top">
        <button type="button" class="voice-assistant-by-site-chat-button-base voice-assistant-by-site-chat-clear">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
            </svg>
        </button>
        <button type="button" class="voice-assistant-by-site-chat-button-base voice-assistant-by-site-chat-close">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor"
                 class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div class="voice-assistant-by-site-chat-inner">
        <div class="voice-assistant-by-site-chat-message voice-assistant-by-site-chat-message-first-static">
            <img class="voice-assistant-by-site-chat-message-icon"
                 src="{{asset('voice-assistant/images/logo.jpg')}}"
                 alt="Логотип нейро-ассистента сайта">
            <div class="voice-assistant-by-site-chat-message-content">
                <p>Привет! Отвечу на все твои вопросы по сайту.</p>
            </div>
        </div>
        <div class="voice-assistant-by-site-chat-messages">
        </div>
    </div>
    <div class="voice-assistant-by-site-chat-bottom">
        <input class="voice-assistant-by-site-chat-bottom-input" type="text" placeholder="Расскажи о вашей компании...">
        <div class="voice-assistant-by-site-chat-bottom-buttons">
            <button class="voice-assistant-by-site-chat-button-base voice-assistant-by-site-controls-send-button"
                    type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                </svg>
            </button>
            <button class="voice-assistant-by-site-chat-button-base voice-assistant-by-site-controls-audio-button"
                    type="button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script src="{{asset('/voice-assistant/js/marked.min.js')}}"></script>
<script src="{{asset('/voice-assistant/js/site-assistant.js')}}"></script>
