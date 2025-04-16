<!DOCTYPE html>
<html class="light-style layout-menu-fixed" data-theme="theme-default" data-assets-path="{{ asset('/assets') . '/' }}" data-base-url="{{url('/')}}" data-framework="laravel" data-template="vertical-menu-laravel-template-free">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>@yield('title') SAgile Project Development Tool </title>
  <meta name="description" content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">
  <!-- laravel CRUD token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Canonical SEO -->
  <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

  <!-- Include Styles -->
  @include('layouts/sections/styles')

  <!-- Include Scripts for customizer, helper, analytics, config -->
  @include('layouts/sections/scriptsIncludes')
</head>
<body>
  <!-- Layout Content -->
  @yield('layoutContent')
  <!--/ Layout Content -->



  <!-- Custom CSS for Dialogflow Messenger -->
  <style>
    df-messenger {
        --df-messenger-bot-message: #f4f4f4;
        --df-messenger-user-message: #dcf8c6;
        --df-messenger-input-box-color: #fff;
        --df-messenger-send-icon: #4CAF50;
        --df-messenger-font-color: #303030;
        --df-messenger-chat-background-color: #ffffff;
        --df-messenger-chat-border-radius: 10px;
        --df-messenger-bot-font-color: #000000;
        --df-messenger-user-font-color: #000000;
        --df-messenger-input-box-font-color: #000000;
    }

    .df-messenger-wrapper {
        position: relative;
    }

    #close-chat-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #f44336;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        text-align: center;
        cursor: pointer;
        z-index: 1000;
    }

    .df-messenger-button-opened {
        height: 300px !important;
        width: 300px !important;
    }

    .df-messenger-wrapper .df-messenger-button {
        height: 50px !important;
        width: 50px !important;
        right: 20px !important;
        bottom: 20px !important;
        border-radius: 50%;
    }

    .df-messenger-wrapper .df-messenger-button svg {
        height: 30px;
        width: 30px;
    }



  </style>

  <!-- Chatbot Script -->
  <script>
    function toggleChatbot() {
      const chatbotContainer = document.getElementById('chatbot-container');
      const chatbotButton = document.getElementById('chatbot-button');
      if (chatbotContainer.style.display === 'none' || chatbotContainer.style.display === '') {
        chatbotContainer.style.display = 'flex';
        chatbotButton.style.display = 'none';
      } else {
        chatbotContainer.style.display = 'none';
        chatbotButton.style.display = 'flex';
      }
    }

    async function sendMessage() {
      const userInput = document.getElementById('userInput').value;
      document.getElementById('userInput').value = '';

      const response = await fetch('/webhook', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          queryInput: {
            text: {
              text: userInput,
              languageCode: 'en'
            }
          }
        })
      });

      if (!response.ok) {
        console.error('Error:', response.statusText);
        return;
      }

      const data = await response.json();
      const botResponse = data.fulfillmentText;

      // Handle displaying the response
      const messages = document.getElementById('messages');
      messages.innerHTML += `<div class="message user"><strong>You:</strong> ${userInput}</div>`;
      messages.innerHTML += `<div class="message bot"><strong>Bot:</strong> ${botResponse}</div>`;
      messages.scrollTop = messages.scrollHeight;
    }
  </script>

    <!-- Include Dialogflow Messenger -->
    <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
    <df-messenger
      intent="WELCOME"
      chat-title="Numby"
      agent-id="2a220d21-7139-464b-a6b6-50c324b6905a"
      language-code="en"
    ></df-messenger>

      <!-- Custom JavaScript for Dialogflow Messenger -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Wait for the Dialogflow Messenger to load
      window.addEventListener('dfMessengerLoaded', function() {
        // Add a close button to the chat window
        const chatWrapper = document.querySelector('df-messenger');
        const closeButton = document.createElement('button');
        closeButton.id = 'close-chat-btn';
        closeButton.innerText = 'X';
        chatWrapper.shadowRoot.querySelector('.df-messenger-wrapper').appendChild(closeButton);

        // Handle close button click
        closeButton.addEventListener('click', function() {
          chatWrapper.shadowRoot.querySelector('.df-messenger-wrapper').classList.remove('df-messenger-wrapper--open');
          chatWrapper.shadowRoot.querySelector('.df-messenger-wrapper').style.display = 'none';
        });

        // Open the chat window when the floating button is clicked
        const openButton = chatWrapper.shadowRoot.querySelector('.df-messenger-button');
        openButton.addEventListener('click', function() {
          chatWrapper.shadowRoot.querySelector('.df-messenger-wrapper').classList.add('df-messenger-wrapper--open');
          chatWrapper.shadowRoot.querySelector('.df-messenger-wrapper').style.display = 'block';
        });
      });
    });
  </script>

  <!-- Include Scripts -->
  @include('layouts/sections/scripts')

</body>
</html>
