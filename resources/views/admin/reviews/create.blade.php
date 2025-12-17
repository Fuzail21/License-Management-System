@extends('layouts.admin')

@section('title', 'Feedback')
@section('page-title', 'Feedback')

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden max-w-2xl mx-auto">
    <div class="px-5 py-4 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Are you satisfied with our services?</h3>
    </div>

    <div class="p-6 space-y-6" x-data="feedbackForm()">

    <!-- Radio Buttons -->
    <div class="flex items-center gap-12"> 
        <label class="flex items-center gap-2 cursor-pointer" style="margin-right: 20px;">
            <input type="radio" name="satisfied" value="1" x-model="satisfied" 
                   class="appearance-none w-5 h-5 border-2 border-gray-300 rounded-full checked:bg-indigo-600 checked:border-indigo-600 focus:outline-none transition duration-200">
            <span class="text-gray-700 font-medium">Yes</span>
        </label>
    
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="satisfied" value="0" x-model="satisfied" 
                   class="appearance-none w-5 h-5 border-2 border-gray-300 rounded-full checked:bg-indigo-600 checked:border-indigo-600 focus:outline-none transition duration-200">
            <span class="text-gray-700 font-medium">No</span>
        </label>
    </div>


    <!-- Submit Button -->
    <div class="flex justify-end">
        <button type="button" @click="submitFeedback()" 
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Submit
        </button>
    </div>
</div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function feedbackForm() {
    return {
        satisfied: null,

        submitFeedback() {
            if (this.satisfied === null) {
                Swal.fire({ icon: 'warning', title: 'Wait!', text: 'Please select an option!' });
                return;
            }

            if (this.satisfied == '1') {
                // 1. Open Google Review immediately
                window.open("https://search.google.com/local/writereview?placeid=ChIJEQxVLk2x3IAR2e4tFEhGbV4", "_blank");

                // 2. Silently save to DB
                fetch("{{ route('admin.feedback.satisfied') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ satisfied: 1 })
                });
                
                Swal.fire('Thank you!', 'We appreciate your review.', 'success');

            } else {
                // NO → Show SweetAlert input popup
                Swal.fire({
                    title: 'We\'re sorry to hear that 😞',
                    html: `

                        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-start;">

                            <select id="issue_type"

                                    style="

                                        width: 100%;

                                        border: 1px solid #d1d5db;

                                        border-radius: 6px;

                                        padding: 0.5rem;

                                        height: 2.5rem;

                                        font-size: 0.875rem;

                                        background-color: white;

                                    ">

                                <option value="" disabled selected>Select Issue Type</option>

                                <option value="Bug">Bug</option>

                                <option value="Performance">Performance</option>

                                <option value="UI">UI Issue</option>

                                <option value="Other">Other</option>

                            </select>



                            <textarea id="issue_message"

                                      placeholder="Please describe your issue here..."

                                      style="

                                        width: 100%;

                                        border: 1px solid #d1d5db;

                                        border-radius: 6px;

                                        padding: 0.5rem;

                                        font-size: 0.875rem;

                                        height: 100px;

                                        resize: none;

                                      ">

                            </textarea>

                        </div>

                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    preConfirm: () => {
                        const issue_type = document.getElementById('issue_type').value;
                        const message = document.getElementById('issue_message').value;
                        if (!issue_type || !message) {
                            Swal.showValidationMessage(`Please fill in both fields`);
                        }
                        return { issue_type, message };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("{{ route('admin.feedback.issue') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                satisfied: 0,
                                issue_type: result.value.issue_type,
                                message: result.value.message
                            })
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json(); // Parse the JSON from Laravel
                        })
                        .then(data => {
                            Swal.fire('Success!', data.message, 'success');
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Something went wrong while saving.', 'error');
                        });
                    }
                });
            }
        }
    }
}
</script>
@endsection
