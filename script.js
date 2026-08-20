document.addEventListener('DOMContentLoaded', function () {
  const heroActions = document.querySelectorAll('.hero-actions .btn');
  heroActions.forEach((button) => {
    button.addEventListener('click', function (event) {
      const href = button.getAttribute('href');
      if (href && href.startsWith('#')) {
        event.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          target.scrollIntoView({ behavior: 'smooth' });
        }
      }
    });
  });
});
