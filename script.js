// Submits form data to the server-side mail handler (send-mail.php)
function submitFormData(formType, data) {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 20000); // 20s safety timeout

  return fetch("send-mail.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ form_type: formType, ...data }),
    signal: controller.signal
  }).then(async (res) => {
    clearTimeout(timeoutId);
    let result;
    try {
      result = await res.json();
    } catch (e) {
      result = { success: false };
    }
    if (!res.ok || !result.success) {
      throw new Error(result.error || "Submission failed");
    }
    return result;
  });
}

function showBanner(form, type, message) {
  // Remove existing banners
  const old = form.querySelector('.form-banner');
  if (old) old.remove();

  const banner = document.createElement('div');
  banner.className = `form-banner ${type}`;
  banner.innerText = message;

  form.prepend(banner);

  // Auto-hide success only
  if (type === 'success') {
    setTimeout(() => {
      banner.style.opacity = '0';
      setTimeout(() => banner.remove(), 300);
    }, 4000);
  }
}

// Mobile menu toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

if (hamburger && navMenu) {
  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
  });
}

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', () => {
    hamburger.classList.remove('active');
    navMenu.classList.remove('active');
  });
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();

    const targetId = this.getAttribute('href');
    if (targetId === '#') return;

    const targetElement = document.querySelector(targetId);
    if (targetElement) {
      window.scrollTo({
        top: targetElement.offsetTop - 80,
        behavior: 'smooth'
      });
    }
  });
});

// Navbar scroll effect
window.addEventListener('scroll', () => {
  const navbar = document.querySelector('.navbar');

  if (!navbar) return;

  if (window.scrollY > 100) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
});

// Tab functionality
document.querySelectorAll('.tab-btn').forEach(button => {
  button.addEventListener('click', () => {
    // Remove active class from all buttons and content
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.classList.remove('active');
    });
    document.querySelectorAll('.tab-content').forEach(content => {
      content.classList.remove('active');
    });

    // Add active class to clicked button
    button.classList.add('active');

    // Show corresponding content
    const tabId = button.getAttribute('data-tab');
    document.querySelectorAll('.tab-btn').forEach(button => {
      button.addEventListener('click', () => {

        document.querySelectorAll('.tab-btn').forEach(btn => {
          btn.classList.remove('active');
        });

        document.querySelectorAll('.tab-content').forEach(content => {
          content.classList.remove('active');
        });

        button.classList.add('active');

        const tabId = button.getAttribute('data-tab');
        const target = document.getElementById(`${tabId}-form`);

        if (target) {
          target.classList.add('active');
        }
      });
    });
  });
});

// General form validation and submission
const generalForm = document.getElementById('general-enrollment-form');

if (generalForm) {
  generalForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const submitBtn = generalForm.querySelector('button[type="submit"]');

    const data = {
      childName: document.getElementById('child-name').value.trim(),
      childAge: document.getElementById('child-age').value,
      dob: document.getElementById('dob').value,
      education: document.getElementById('education').value.trim(),
      parentName: document.getElementById('parent-name').value.trim(),
      email: document.getElementById('email').value.trim(),
      phone: document.getElementById('phone').value.trim(),
      country: document.getElementById('country').value,
      program: document.getElementById('program').value
    };

    // VALIDATION
    if (
      !data.childName ||
      !data.dob ||
      !data.parentName ||
      !data.phone ||
      !isValidEmail(data.email) ||
      !data.country ||
      !data.program
    ) {
      alert("Please complete all required fields correctly.");
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerText = "Submitting...";

    submitFormData("general", data)
      .then(() => {
        showBanner(generalForm, "success", "Application submitted successfully.");
        generalForm.reset();
      })
      .catch((err) => {
        console.error(err);
        showBanner(generalForm, "error", "Submission failed. Please try again.");
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerText = "Submit Enrollment Request";
      });
  });
}

// After School form validation and submission
const afterSchoolForm = document.getElementById('after-school-enrollment-form');

if (afterSchoolForm) {
  afterSchoolForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const submitBtn = afterSchoolForm.querySelector('button[type="submit"]');

    const data = {
      childFirstName: document.getElementById('as-child-fname').value.trim(),
      childSurname: document.getElementById('as-child-sname').value.trim(),
      asDob: document.getElementById('as-dob').value,
      currentSchool: document.getElementById('as-current-school').value.trim(),
      asParentName: document.getElementById('as-parent-name').value.trim(),
      asPhone: document.getElementById('as-phone').value.trim(),
      asAddress: document.getElementById('as-address').value.trim(),
      asProgram: document.getElementById('as-program').value
    };

    // VALIDATION
    if (
      !data.childFirstName ||
      !data.childSurname ||
      !data.currentSchool ||
      !data.asDob ||
      !data.asParentName ||
      !data.asPhone ||
      !data.asAddress ||
      !data.asProgram
    ) {
      alert("Please complete all required fields correctly.");
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerText = "Submitting...";

    submitFormData("after-school", data)
      .then(() => {
        showBanner(afterSchoolForm, "success", "After School application submitted successfully.");
        afterSchoolForm.reset();
      })
      .catch(err => {
        console.error(err);
        showBanner(afterSchoolForm, "error", "Submission failed. Please try again.");
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerText = "Submit After School Enrollment";
      });
  });
}

// Post-Secondary form submission (EmailJS)
const postSecondaryForm = document.getElementById('post-secondary-enrollment-form');

if (postSecondaryForm) {
  postSecondaryForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const submitBtn = postSecondaryForm.querySelector('button[type="submit"]');

    const data = {
      psFirstName: document.getElementById('ps-first-name').value.trim(),
      psLastName: document.getElementById('ps-last-name').value.trim(),
      psPhone: document.getElementById('ps-phone').value.trim(),
      psBackground: document.getElementById('ps-background').value,
      psProgram: document.getElementById('ps-program').value
    };

    // VALIDATION
    if (
      !data.psFirstName ||
      !data.psLastName ||
      !data.psPhone ||
      !data.psBackground ||
      !data.psProgram
    ) {
      alert("Please complete all required fields correctly.");
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerText = "Sending...";

    submitFormData("post-secondary", data)
      .then(() => {
        showBanner(postSecondaryForm, "success", "Post-Secondary application submitted successfully.");
        postSecondaryForm.reset();
      })
      .catch(err => {
        console.error(err);
        showBanner(postSecondaryForm, "error", "Submission failed. Please try again.");
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerText = "Submit Post-Secondary Enrollment";
      });
  });
}

function isValidEmail(email) {
  const emailRegex = /^[\w\.-]+@[\w\.-]+\.[\w]+$/;
  return emailRegex.test(email);
}

// Animation on scroll
function animateOnScroll() {
  const elements = document.querySelectorAll('.card, .curriculum-card, .contact-item');

  elements.forEach(element => {
    const elementPosition = element.getBoundingClientRect().top;
    const screenPosition = window.innerHeight / 1.3;

    if (elementPosition < screenPosition) {
      element.style.opacity = '1';
      element.style.transform = 'translateY(0)';
    }
  });
}

// Set initial styles for animation
document.querySelectorAll('.card, .curriculum-card, .contact-item').forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(20px)';
  el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
});

window.addEventListener('scroll', animateOnScroll);
// Trigger once on load
window.addEventListener('load', animateOnScroll);

// Contact form validation and submission
const contactForm = document.getElementById('contact-form');

if (contactForm) {
  contactForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const submitBtn = contactForm.querySelector('button[type="submit"]');

    const data = {
      name: document.getElementById('contact-name').value.trim(),
      email: document.getElementById('contact-email').value.trim(),
      phone: document.getElementById('contact-phone').value.trim(),
      subject: document.getElementById('contact-subject').value,
      message: document.getElementById('contact-message').value.trim()
    };

    if (!data.name || !isValidEmail(data.email) || !data.subject || !data.message) {
      alert("Please complete all required fields correctly.");
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerText = "Sending...";

    submitFormData("contact", data)
      .then(() => {
        showBanner(contactForm, "success", "Thank you! Your message has been sent. We'll get back to you shortly.");
        contactForm.reset();
      })
      .catch((err) => {
        console.error(err);
        showBanner(contactForm, "error", "Submission failed. Please try again.");
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerText = "Send Message";
      });
  });
}