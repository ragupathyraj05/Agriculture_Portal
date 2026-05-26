// Main JS file
console.log('Agriculture Portal Loaded');

// Add any global interactive logic here
// For example, smooth scrolling or dynamic UI updates

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
