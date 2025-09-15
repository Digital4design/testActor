<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <title>Actor Submission</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div id="app" class="w-full max-w-lg bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Actor Submission</h1>

        <p class="text-gray-500 mb-4 text-sm">
            Please enter your first name and last name, and also provide your address.
        </p>

        <form @submit.prevent="submitForm" class="space-y-4">
            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input v-model="form.email" type="email" required
                    class="w-full p-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300">
            </div>

            <!-- Actor Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Actor Description</label>
                <textarea v-model="form.description" rows="3" required
                    class="w-full p-2 border rounded-md focus:outline-none focus:ring focus:border-blue-300"></textarea>
            </div>

            <!-- Errors -->
            <div v-if="errors.length" class="text-red-600 text-sm space-y-1">
                <div v-for="(e,i) in errors" :key="i">@{{ e }}</div>
            </div>

            <!-- Missing Fields -->
            <div v-if="missingFields.length" class="text-amber-700 text-sm">
                Missing in description: <strong>@{{ missingFields.join(', ') }}</strong>
            </div>

            <!-- Submit -->
            <button type="submit" :disabled="isSubmitting"
                class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
                @{{ isSubmitting ? 'Submitting…' : 'Submit' }}
            </button>
        </form>
    </div>

    <script>
        const {
            createApp
        } = Vue;

        createApp({
            data() {
                return {
                    form: {
                        email: '',
                        description: ''
                    },
                    errors: [],
                    missingFields: [],
                    isSubmitting: false,
                    emailAvailable: null,
                    emailTimer: null,
                    descTimer: null,
                };
            },
            watch: {
                'form.email'(v) {
                    clearTimeout(this.emailTimer);
                    this.emailTimer = setTimeout(() => this.checkEmail(), 500);
                },
                'form.description'(v) {
                    clearTimeout(this.descTimer);
                    this.descTimer = setTimeout(() => this.checkDescription(), 500);
                }
            },
            methods: {
                csrf() {
                    return document.querySelector('meta[name="csrf-token"]').content;
                },

                async checkEmail() {
                    this.emailAvailable = null;
                    if (!this.form.email) return;
                    try {
                        const res = await fetch('/api/actors/check-email', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            },
                            body: JSON.stringify({
                                email: this.form.email
                            })
                        });
                        const json = await res.json();
                        this.emailAvailable = json.available;
                        if (!json.available) {
                            this.errors = [json.message || 'Email already used'];
                        } else {
                            this.errors = this.errors.filter(e => !e.toLowerCase().includes('email'));
                        }
                    } catch {
                        this.errors = ['Could not verify email (network error).'];
                    }
                },

                async checkDescription() {
                    this.missingFields = [];
                    if (!this.form.description) return;
                    try {
                        const res = await fetch('/api/actors/check-description', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            },
                            body: JSON.stringify({
                                description: this.form.description
                            })
                        });
                        const json = await res.json();
                        if (json.valid) {
                            this.missingFields = [];
                        } else {
                            this.missingFields = json.missing || [];
                        }
                    } catch {
                        this.errors = ['Could not validate description (network error).'];
                    }
                },

                async validateBeforeSubmit() {
                    this.errors = [];
                    this.missingFields = [];

                    const checks = await Promise.allSettled([
                        fetch('/api/actors/check-email', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            },
                            body: JSON.stringify({
                                email: this.form.email
                            })
                        }).then(r => r.json()),

                        fetch('/api/actors/check-description', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            },
                            body: JSON.stringify({
                                description: this.form.description
                            })
                        }).then(r => r.json()),
                    ]);

                    if (checks[0].status === 'fulfilled') {
                        const e = checks[0].value;
                        if (!e.available) this.errors.push(e.message || 'Email already in use');
                    } else {
                        this.errors.push('Could not verify email.');
                    }

                    if (checks[1].status === 'fulfilled') {
                        const d = checks[1].value;
                        if (!d.valid) {
                            this.missingFields = d.missing || [];
                            const must = ['First Name', 'Last Name', 'Address'];
                            if (must.some(x => this.missingFields.includes(x))) {
                                this.errors.push(
                                    'Please add first name, last name, and address to your description.');
                            } else {
                                this.errors.push('Please add: ' + (this.missingFields.join(', ')));
                            }
                        }
                    } else {
                        this.errors.push('Could not validate description.');
                    }

                    return this.errors.length === 0;
                },

                async submitForm() {
                    this.isSubmitting = true;
                    this.errors = [];

                    if (!this.form.email || !this.form.description) {
                        this.errors.push('Email and description are required.');
                        this.isSubmitting = false;
                        return;
                    }

                    const ok = await this.validateBeforeSubmit();
                    if (!ok) {
                        this.isSubmitting = false;
                        return;
                    }

                    try {
                        const params = new URLSearchParams({
                            email: this.form.email,
                            description: this.form.description
                        });

                        const res = await fetch(`/api/actors/prompt-validation?${params.toString()}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf()
                            }
                        });

                        const json = await res.json();
                        if (!res.ok) {
                            if (json.errors) {
                                this.errors = Object.values(json.errors).flat();
                            } else {
                                this.errors = [json.message || 'Submit failed'];
                            }
                            this.isSubmitting = false;
                            return;
                        }

                        window.location.href = '/actors';
                    } catch {
                        this.errors = ['Server error. Try again later.'];
                        this.isSubmitting = false;
                    }
                }
            }
        }).mount('#app');
    </script>

</body>

</html>
