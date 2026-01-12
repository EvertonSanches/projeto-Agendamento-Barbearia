document.addEventListener("DOMContentLoaded", () => {
    const card = document.querySelector('.login-card');
    if (!card) return;

   
    card.style.opacity = '';
    card.style.transform = '';

    
    requestAnimationFrame(() => {
        card.setAttribute('data-state', 'visible');
    });
});
