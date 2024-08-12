let deleteButtons = document.querySelectorAll('.delete-btn');
let deleteBox = document.querySelector('.delete-box');
let confirmButton = deleteBox.querySelector('.confirm-btn');
let cancelButton = deleteBox.querySelector('.cancel-btn');
let deleteLink = '';

deleteButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        deleteLink = this.getAttribute('href');
        deleteBox.classList.add('flex');
        deleteBox.classList.remove('hidden');
    });
});

confirmButton.addEventListener('click', function() {
    window.location.href = deleteLink;
});

cancelButton.addEventListener('click', function() {
    deleteBox.classList.add('hidden');
    deleteBox.classList.remove('flex');
});

window.addEventListener('scroll', function() {
    deleteBox.classList.add('hidden');
    deleteBox.classList.remove('flex');
});