        // Select the password input and the toggle icon
        const passwordInput = document.getElementById('password');
        const togglePasswordIcon = document.getElementById('toggle-password');

        // Add click event listener to the icon
        togglePasswordIcon.addEventListener('click', () => {
            // Check the current type of the password input
            const isPasswordVisible = passwordInput.type === 'text';
            
            // Toggle the input type
            passwordInput.type = isPasswordVisible ? 'password' : 'text';

            // Toggle the icon class between bx-hide and bx-show
            togglePasswordIcon.classList.toggle('bx-hide', isPasswordVisible);
            togglePasswordIcon.classList.toggle('bx-show', !isPasswordVisible);
        });