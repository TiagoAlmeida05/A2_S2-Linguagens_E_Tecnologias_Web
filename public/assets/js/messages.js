(() => {
  const currentUserId = window.currentUserId;
  if (!currentUserId) {
    console.error('No current user ID found.');
    return;
  }

  let selectedUserId = null;

  const messagesList = document.getElementById('messages-list');
  const messageForm = document.getElementById('message-form');
  const messageContent = document.getElementById('message-content');
  const conversationTitle = document.getElementById('conversation-title');
  const startNewBtn = document.getElementById('start-new-btn');
  const newUserList = document.getElementById('new-user-list');

  function loadMessages() {
    if (!selectedUserId) return;

    fetch(`/pages/fetch_messages.php?user=${selectedUserId}`)
      .then(res => res.json())
      .then(data => {
        messagesList.innerHTML = '';

        if (data.length === 0) {
          messagesList.innerHTML = '<p>No messages yet. Start the conversation!</p>';
          return;
        }

        data.forEach(msg => {
          fetch(`/pages/mark_read.php?user=${selectedUserId}`, { method: 'POST' });
          const div = document.createElement('div');
          div.className = 'message ' + (msg.sender_id == currentUserId ? 'sent' : 'received');
          div.innerHTML = `
            <strong>${msg.sender_username}:</strong>
            <p>${msg.content}</p>
            <small>${new Date(msg.sent_at).toLocaleString()}</small>
          `;
          messagesList.appendChild(div);
        });

        messagesList.scrollTop = messagesList.scrollHeight;
      })
      .catch(err => console.error('Failed to load messages:', err));
  }

  function sendMessage(e) {
    e.preventDefault();
    if (!selectedUserId) return;

    const content = messageContent.value.trim();
    if (!content) return;

    fetch('/pages/send_message.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ receiver_id: selectedUserId, content })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          messageContent.value = '';
          autoExpand(messageContent);
          loadMessages();
        } else {
          console.error('Failed to send message:', data.error || 'Unknown error');
        }
      })
      .catch(err => console.error('Error sending message:', err));
  }

  function handleUserClick(e) {
    e.preventDefault();

    selectedUserId = this.getAttribute('data-id');
    conversationTitle.textContent = `Conversation with ${this.textContent}`;
    messageForm.style.display = 'flex';
    messageContent.focus();
    loadMessages();

    // Update URL param without reload
    const newUrl = new URL(window.location);
    newUrl.searchParams.set('user', selectedUserId);
    window.history.replaceState({}, '', newUrl);
  }

  function toggleNewUserList() {
    if (newUserList) {
      newUserList.style.display = newUserList.style.display === 'none' ? 'block' : 'none';
    }
  }

  function autoExpand(field) {
    field.style.height = 'auto';
    field.style.height = field.scrollHeight + 'px';
  }

  function init() {
    document.querySelectorAll('.user-link').forEach(link => {
      link.addEventListener('click', handleUserClick);
    });

    messageForm.addEventListener('submit', sendMessage);
    messageContent.addEventListener('input', () => autoExpand(messageContent));

    if (startNewBtn) {
      startNewBtn.addEventListener('click', toggleNewUserList);
    }

    messageForm.style.display = 'none';

    if (window.prefilledUserId !== null) {
      selectedUserId = window.prefilledUserId;

      const userLink = document.querySelector(`.user-link[data-id="${selectedUserId}"]`);
      if (userLink) {
        conversationTitle.textContent = 'Conversation with ' + userLink.textContent;
      } else {
        conversationTitle.textContent = 'Conversation';
      }

      messageForm.style.display = 'flex';
      messageContent.focus();
      loadMessages();
    }

    setInterval(() => {
      if (selectedUserId) loadMessages();
    }, 5000);
  }

  document.addEventListener('DOMContentLoaded', init);
})();
