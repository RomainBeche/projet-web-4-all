const Validator = {

    // 🔹 Required field
    required(value) {
        return value !== null && value !== undefined && value.toString().trim() !== '';
    },

    // 🔹 Email validation
    email(value) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(value);
    },

    // 🔹 Password validation
    password(value, min = 6) {
        return value && value.length >= min;
    },

    // 🔹 URL validation
    url(value) {
        try {
            new URL(value);
            return true;
        } catch {
            return false;
        }
    },

    // 🔹 File validation
    file(file, options = {}) {

        if (!file) return false;

        const {
            maxSize = 2 * 1024 * 1024, // 2MB
            types = ['application/pdf']
        } = options;

        if (types.length && !types.includes(file.type)) {
            return false;
        }

        if (file.size > maxSize) {
            return false;
        }

        return true;
    },

    // 🔹 Date not in past
    futureDate(value) {
        const selected = new Date(value);
        const today = new Date();
        today.setHours(0,0,0,0);
        return selected >= today;
    }
};

// On le rend global
window.Validator = Validator;