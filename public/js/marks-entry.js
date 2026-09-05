document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    function getErrorDiv(input) {
        if (!input) {
            return null;
        }

        return input.parentNode
            ? input.parentNode.querySelector('.error-text')
            : null;
    }


    function clearMarkError(input) {

        if (!input) {
            return;
        }

        const errorDiv = getErrorDiv(input);

        if (errorDiv) {
            errorDiv.textContent = '';
        }

        input.style.border = '1px solid #9CA3AF';
    }


    function showMarkError(input, message) {

        if (!input) {
            return;
        }

        const errorDiv = getErrorDiv(input);

        input.style.border = '2px solid #DC2626';

        if (errorDiv) {
            errorDiv.textContent = message;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MAXIMUM MARKS
    |--------------------------------------------------------------------------
    */

    function getMaxMarks(input) {

        if (!input) {
            return 0;
        }

        const max =
            input.dataset.max ||
            input.getAttribute('max') ||
            0;

        const parsed = Number(max);

        return Number.isFinite(parsed)
            ? parsed
            : 0;
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE VALIDATION
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | SAVE DOES NOT REQUIRE MARKS.
    |
    | Empty textbox = VALID.
    |
    */

    function validateMarkForSave(input) {

        if (!input || input.readOnly || input.disabled) {
            return true;
        }

        const value = input.value.trim();

        clearMarkError(input);


        /*
        |--------------------------------------------------------------------------
        | EMPTY IS ALLOWED DURING SAVE
        |--------------------------------------------------------------------------
        */

        if (value === '') {
            return true;
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE: ONLY NUMERIC VALUES
        |--------------------------------------------------------------------------
        |
        | Decimal is allowed during SAVE if your database/helper allows it.
        | Final SUBMIT will enforce integer digits only.
        |
        */

        if (!/^\d+(\.\d+)?$/.test(value)) {

            showMarkError(
                input,
                'Enter a valid numeric mark.'
            );

            return false;
        }


        const numericValue = Number(value);
        const max = getMaxMarks(input);


        if (!Number.isFinite(numericValue)) {

            showMarkError(
                input,
                'Enter a valid numeric mark.'
            );

            return false;
        }


        if (numericValue < 0) {

            showMarkError(
                input,
                'Marks cannot be negative.'
            );

            return false;
        }


        if (numericValue > max) {

            showMarkError(
                input,
                'Maximum allowed marks is ' + max + '.'
            );

            return false;
        }


        input.style.border = '2px solid #16A34A';

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | FINAL SUBMIT VALIDATION
    |--------------------------------------------------------------------------
    |
    | ONLY DIGITS ARE ALLOWED.
    |
    | Valid:
    | 0
    | 1
    | 25
    | 100
    |
    | Invalid:
    | 10.5
    | -5
    | abc
    | 12abc
    | 10@
    | 1 2
    |
    */

    function validateMarkForSubmit(input) {

        if (!input || input.readOnly || input.disabled) {
            return true;
        }

        const value = input.value.trim();

        clearMarkError(input);


        /*
        |--------------------------------------------------------------------------
        | EMPTY
        |--------------------------------------------------------------------------
        */

        if (value === '') {

            showMarkError(
                input,
                'Marks are required.'
            );

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | DIGITS ONLY
        |--------------------------------------------------------------------------
        */

        if (!/^\d+$/.test(value)) {

            showMarkError(
                input,
                'Only whole-number digits are allowed.'
            );

            return false;
        }


        const numericValue = Number(value);
        const max = getMaxMarks(input);


        /*
        |--------------------------------------------------------------------------
        | MAXIMUM
        |--------------------------------------------------------------------------
        */

        if (numericValue > max) {

            showMarkError(
                input,
                'Maximum allowed marks is ' + max + '.'
            );

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        input.style.border = '2px solid #16A34A';

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | GLOBAL FUNCTION
    |--------------------------------------------------------------------------
    */

    window.validateMarkInput = validateMarkForSave;


    /*
    |--------------------------------------------------------------------------
    | GET STUDENT INPUTS
    |--------------------------------------------------------------------------
    */

    function getStudentMarkInputs(studentId) {

        return document.querySelectorAll(
            '.student-' + studentId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ENABLE STUDENT MARKS
    |--------------------------------------------------------------------------
    */

    function enableStudentMarks(studentId) {

        const inputs =
            getStudentMarkInputs(studentId);

        inputs.forEach(function (input) {

            input.readOnly = false;
            input.disabled = false;

            /*
             * IMPORTANT:
             *
             * Do NOT use:
             *
             * input.required = true;
             *
             * because SAVE must allow empty values.
             */

            input.required = false;

            input.style.background = '';
            input.style.border =
                '1px solid #9CA3AF';

            clearMarkError(input);


            /*
             * Remove automatically inserted zero
             * when returning from ABSENT/OPTIONAL.
             */

            if (input.value === '0') {
                input.value = '';
            }

        });
    }


    /*
    |--------------------------------------------------------------------------
    | DISABLE STUDENT MARKS
    |--------------------------------------------------------------------------
    */

    function disableStudentMarks(
        studentId,
        backgroundColor,
        borderColor
    ) {

        const inputs =
            getStudentMarkInputs(studentId);

        inputs.forEach(function (input) {

            input.value = '0';

            input.readOnly = true;

            /*
             * Keep enabled so the value is submitted.
             */
            input.disabled = false;

            input.required = false;

            input.style.background =
                backgroundColor;

            input.style.border =
                '1px solid ' + borderColor;

            clearMarkError(input);

        });
    }


    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE BUTTON
    |--------------------------------------------------------------------------
    */

    function updateAttendanceButton(
        studentId,
        isAbsent
    ) {

        const button =
            document.getElementById(
                'btn_' + studentId
            );

        if (!button) {
            return;
        }

        button.type = 'button';

        if (isAbsent) {

            button.textContent = 'ABSENT';

            button.classList.remove(
                'present-btn'
            );

            button.classList.add(
                'absent-btn'
            );

        } else {

            button.textContent = 'PRESENT';

            button.classList.remove(
                'absent-btn'
            );

            button.classList.add(
                'present-btn'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL BUTTON
    |--------------------------------------------------------------------------
    */

    function updateOptionalButton(
        studentId,
        isOptional
    ) {

        const button =
            document.getElementById(
                'optional_btn_' + studentId
            );

        if (!button) {
            return;
        }

        button.type = 'button';

        if (isOptional) {

            button.textContent = 'OPTIONAL';

            button.classList.add(
                'optional-active-btn'
            );

        } else {

            button.textContent = 'NORMAL';

            button.classList.remove(
                'optional-active-btn'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS DISPLAY
    |--------------------------------------------------------------------------
    */

    function updateStudentStatus(
        studentId,
        statusType
    ) {

        const status =
            document.getElementById(
                'status_' + studentId
            );

        if (!status) {
            return;
        }

        let html = '';

        if (statusType === 'ABSENT') {

            html =
                '<div class="status-cell-wrapper">' +
                    '<span class="status-absent">ABSENT</span>' +
                '</div>';

        } else if (statusType === 'OPTIONAL') {

            html =
                '<div class="status-cell-wrapper">' +
                    '<span class="status-optional">OPTIONAL</span>' +
                '</div>';

        } else {

            html =
                '<div class="status-cell-wrapper">' +
                    '<span class="status-present">PRESENT</span>' +
                '</div>';
        }

        status.innerHTML = html;
    }


    /*
    |--------------------------------------------------------------------------
    | NORMAL / PRESENT
    |--------------------------------------------------------------------------
    */

    window.setNormalStatus = function (studentId) {

        const absentFlag =
            document.getElementById(
                'absent_' + studentId
            );

        const optionalFlag =
            document.getElementById(
                'optional_' + studentId
            );

        if (absentFlag) {
            absentFlag.value = '0';
        }

        if (optionalFlag) {
            optionalFlag.value = '0';
        }

        updateAttendanceButton(
            studentId,
            false
        );

        updateOptionalButton(
            studentId,
            false
        );

        updateStudentStatus(
            studentId,
            'PRESENT'
        );

        enableStudentMarks(studentId);
    };


    /*
    |--------------------------------------------------------------------------
    | ABSENT
    |--------------------------------------------------------------------------
    */

    window.makeAbsent = function (studentId) {

        const absentFlag =
            document.getElementById(
                'absent_' + studentId
            );

        const optionalFlag =
            document.getElementById(
                'optional_' + studentId
            );

        if (!absentFlag) {
            return;
        }

        absentFlag.value = '1';

        if (optionalFlag) {
            optionalFlag.value = '0';
        }

        updateAttendanceButton(
            studentId,
            true
        );

        updateOptionalButton(
            studentId,
            false
        );

        updateStudentStatus(
            studentId,
            'ABSENT'
        );

        disableStudentMarks(
            studentId,
            '#fee2e2',
            '#fca5a5'
        );
    };


    /*
    |--------------------------------------------------------------------------
    | PRESENT
    |--------------------------------------------------------------------------
    */

    window.makePresent = function (studentId) {

        const absentFlag =
            document.getElementById(
                'absent_' + studentId
            );

        const optionalFlag =
            document.getElementById(
                'optional_' + studentId
            );

        if (!absentFlag) {
            return;
        }

        absentFlag.value = '0';

        if (optionalFlag) {
            optionalFlag.value = '0';
        }

        updateAttendanceButton(
            studentId,
            false
        );

        updateOptionalButton(
            studentId,
            false
        );

        updateStudentStatus(
            studentId,
            'PRESENT'
        );

        enableStudentMarks(studentId);
    };


    /*
    |--------------------------------------------------------------------------
    | TOGGLE ABSENT
    |--------------------------------------------------------------------------
    */

    window.toggleAbsent = function (
        studentId,
        event
    ) {

        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const absentFlag =
            document.getElementById(
                'absent_' + studentId
            );

        const optionalFlag =
            document.getElementById(
                'optional_' + studentId
            );

        if (!absentFlag) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL -> ABSENT
        |--------------------------------------------------------------------------
        */

        if (
            optionalFlag &&
            optionalFlag.value === '1'
        ) {

            if (typeof Swal === 'undefined') {

                setNormalStatus(studentId);

                setTimeout(function () {
                    makeAbsent(studentId);
                }, 0);

                return false;
            }

            Swal.fire({

                icon: 'question',

                title: 'Student is Optional',

                text:
                    'Remove Optional status before changing Attendance?',

                showCancelButton: true,

                confirmButtonText:
                    'Yes, Continue',

                cancelButtonText:
                    'Cancel'

            }).then(function (result) {

                if (!result.isConfirmed) {
                    return;
                }

                setNormalStatus(studentId);

                setTimeout(function () {
                    makeAbsent(studentId);
                }, 50);

            });

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | PRESENT -> ABSENT
        |--------------------------------------------------------------------------
        */

        if (absentFlag.value === '0') {

            if (typeof Swal === 'undefined') {

                makeAbsent(studentId);

                return false;
            }

            Swal.fire({

                icon: 'warning',

                title: 'Confirm Absent',

                text:
                    'Student will be marked ABSENT and all marks will become 0.',

                showCancelButton: true,

                confirmButtonText:
                    'Yes, Mark Absent',

                cancelButtonText:
                    'Cancel'

            }).then(function (result) {

                if (result.isConfirmed) {
                    makeAbsent(studentId);
                }

            });

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | ABSENT -> PRESENT
        |--------------------------------------------------------------------------
        */

        if (typeof Swal === 'undefined') {

            makePresent(studentId);

            return false;
        }

        Swal.fire({

            icon: 'question',

            title: 'Confirm Present',

            text:
                'Change student status back to PRESENT?',

            showCancelButton: true,

            confirmButtonText:
                'Yes, Present',

            cancelButtonText:
                'Cancel'

        }).then(function (result) {

            if (result.isConfirmed) {
                makePresent(studentId);
            }

        });

        return false;
    };


    /*
    |--------------------------------------------------------------------------
    | MAKE OPTIONAL
    |--------------------------------------------------------------------------
    */

    window.makeOptional = function (studentId) {

        const optionalFlag =
            document.getElementById(
                'optional_' + studentId
            );

        const absentFlag =
            document.getElementById(
                'absent_' + studentId
            );

        if (!optionalFlag) {
            return;
        }

        optionalFlag.value = '1';

        if (absentFlag) {
            absentFlag.value = '0';
        }

        updateOptionalButton(
            studentId,
            true
        );

        updateAttendanceButton(
            studentId,
            false
        );

        updateStudentStatus(
            studentId,
            'OPTIONAL'
        );

        disableStudentMarks(
            studentId,
            '#fff7ed',
            '#f59e0b'
        );
    };


    /*
    |--------------------------------------------------------------------------
    | TOGGLE OPTIONAL
    |--------------------------------------------------------------------------
    */

    window.toggleOptional = function (
        studentId,
        event
    ) {

        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const optionalFlag =
            document.getElementById(
                'optional_' + studentId
            );

        const absentFlag =
            document.getElementById(
                'absent_' + studentId
            );

        if (!optionalFlag) {
            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | ABSENT CANNOT BE OPTIONAL
        |--------------------------------------------------------------------------
        */

        if (
            absentFlag &&
            absentFlag.value === '1'
        ) {

            if (typeof Swal !== 'undefined') {

                Swal.fire({

                    icon: 'warning',

                    title: 'Student is Absent',

                    text:
                        'An absent student cannot be marked as Optional.',

                    confirmButtonText:
                        'OK'
                });

            }

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | NORMAL -> OPTIONAL
        |--------------------------------------------------------------------------
        */

        if (optionalFlag.value === '0') {

            if (typeof Swal === 'undefined') {

                makeOptional(studentId);

                return false;
            }

            Swal.fire({

                icon: 'warning',

                title: 'Mark Student Optional?',

                text:
                    'This student will be excluded from marks calculation for this subject.',

                showCancelButton: true,

                confirmButtonText:
                    'Yes, Optional',

                cancelButtonText:
                    'Cancel',

                confirmButtonColor:
                    '#d97706'

            }).then(function (result) {

                if (result.isConfirmed) {
                    makeOptional(studentId);
                }

            });

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | OPTIONAL -> NORMAL
        |--------------------------------------------------------------------------
        */

        if (typeof Swal === 'undefined') {

            setNormalStatus(studentId);

            return false;
        }

        Swal.fire({

            icon: 'question',

            title: 'Remove Optional Status?',

            text:
                'This student will become a normal PRESENT student.',

            showCancelButton: true,

            confirmButtonText:
                'Yes, Normal',

            cancelButtonText:
                'Cancel'

        }).then(function (result) {

            if (result.isConfirmed) {
                setNormalStatus(studentId);
            }

        });

        return false;
    };


    /*
    |--------------------------------------------------------------------------
    | MARK INPUT EVENTS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | DO NOT MAKE EMPTY INPUT INVALID HERE.
    |
    */

    document
        .querySelectorAll('.mark-input')
        .forEach(function (input) {

            input.addEventListener(
                'input',
                function () {

                    /*
                     * Save-side validation only.
                     * Empty is allowed.
                     */
                    validateMarkForSave(this);
                }
            );

            input.addEventListener(
                'blur',
                function () {

                    validateMarkForSave(this);
                }
            );
        });


    /*
    |--------------------------------------------------------------------------
    | FORCE BUTTON TYPE
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '[id^="btn_"], [id^="optional_btn_"]'
        )
        .forEach(function (button) {

            button.type = 'button';

        });


    /*
    |--------------------------------------------------------------------------
    | SAVE FORM
    |--------------------------------------------------------------------------
    |
    | SAVE:
    |
    | Empty = ALLOWED
    |
    */

    const saveForm =
        document.getElementById(
            'marksEntryForm'
        );


    if (saveForm) {

        saveForm.addEventListener(
            'submit',
            function (event) {

                let valid = true;


                document
                    .querySelectorAll(
                        '.marks-table tbody tr'
                    )
                    .forEach(function (row) {

                        const absentField =
                            row.querySelector(
                                'input[id^="absent_"]'
                            );

                        const optionalField =
                            row.querySelector(
                                'input[id^="optional_"]'
                            );


                        const isAbsent =
                            absentField &&
                            absentField.value === '1';

                        const isOptional =
                            optionalField &&
                            optionalField.value === '1';


                        /*
                         * ABSENT / OPTIONAL
                         */

                        if (
                            isAbsent ||
                            isOptional
                        ) {
                            return;
                        }


                        /*
                         * SAVE VALIDATION
                         *
                         * Empty values are allowed.
                         */

                        row
                            .querySelectorAll('.mark-input')
                            .forEach(function (input) {

                                if (
                                    input.readOnly ||
                                    input.disabled
                                ) {
                                    return;
                                }

                                if (
                                    !validateMarkForSave(input)
                                ) {

                                    valid = false;
                                }

                            });

                    });


                if (!valid) {

                    event.preventDefault();

                    if (typeof Swal !== 'undefined') {

                        Swal.fire({

                            icon: 'error',

                            title: 'Invalid Marks',

                            text:
                                'Please correct the entered marks.',

                            confirmButtonText:
                                'OK'
                        });

                    } else {

                        alert(
                            'Please correct the entered marks.'
                        );
                    }

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | PREVENT DOUBLE SAVE
                |--------------------------------------------------------------------------
                */

                const saveButton =
                    document.getElementById(
                        'saveMarksButton'
                    );

                if (saveButton) {

                    saveButton.disabled = true;

                    saveButton.innerText =
                        'Saving...';
                }

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FINAL SUBMIT FORM
    |--------------------------------------------------------------------------
    */

    const submitFinalButton =
        document.getElementById(
            'submitFinalButton'
        );

    const finalSubmitForm =
        document.getElementById(
            'finalSubmitForm'
        );


    if (
        submitFinalButton &&
        finalSubmitForm
    ) {

        submitFinalButton.type = 'button';


        submitFinalButton.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();


                if (
                    submitFinalButton.disabled
                ) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | FIRST: VALIDATE ALL PRESENT STUDENTS
                |--------------------------------------------------------------------------
                */

                let valid = true;

                let firstInvalidInput = null;


                /*
                 * Support either:
                 *
                 * #marksEntryForm
                 * #marksSaveForm
                 *
                 */

                const marksContainer =
                    document.querySelector(
                        '#marksEntryForm tbody'
                    ) ||
                    document.querySelector(
                        '#marksSaveForm tbody'
                    ) ||
                    document.querySelector(
                        '.marks-table tbody'
                    );


                if (marksContainer) {

                    marksContainer
                        .querySelectorAll('tr')
                        .forEach(function (row) {

                            const optionalField =
                                row.querySelector(
                                    'input[name^="is_optional["], input[id^="optional_"]'
                                );

                            const absentField =
                                row.querySelector(
                                    'input[name^="is_absent["], input[id^="absent_"]'
                                );


                            const isOptional =
                                optionalField &&
                                optionalField.value === '1';

                            const isAbsent =
                                absentField &&
                                absentField.value === '1';


                            /*
                             * OPTIONAL / ABSENT
                             *
                             * Marks are not required.
                             */

                            if (
                                isOptional ||
                                isAbsent
                            ) {
                                return;
                            }


                            /*
                             * PRESENT STUDENT
                             *
                             * Every mark field must contain
                             * whole-number digits only.
                             */

                            row
                                .querySelectorAll('.mark-input')
                                .forEach(function (input) {

                                    if (
                                        input.readOnly ||
                                        input.disabled
                                    ) {
                                        return;
                                    }


                                    if (
                                        !validateMarkForSubmit(input)
                                    ) {

                                        valid = false;

                                        if (
                                            !firstInvalidInput
                                        ) {

                                            firstInvalidInput =
                                                input;
                                        }
                                    }

                                });

                        });
                }


                /*
                |--------------------------------------------------------------------------
                | VALIDATION FAILED
                |--------------------------------------------------------------------------
                */

                if (!valid) {

                    if (
                        firstInvalidInput
                    ) {

                        firstInvalidInput.focus();

                        firstInvalidInput.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }


                    if (
                        typeof Swal !== 'undefined'
                    ) {

                        Swal.fire({

                            icon: 'error',

                            title: 'Validation Error',

                            html:
                                'Please correct all marks before final submission.<br><br>' +
                                '<b>Only whole-number digits are allowed.</b>',

                            confirmButtonText:
                                'OK'
                        });

                    } else {

                        alert(
                            'Please correct all marks before final submission.'
                        );
                    }

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | FINAL CONFIRMATION
                |--------------------------------------------------------------------------
                */

                if (
                    typeof Swal === 'undefined'
                ) {

                    if (
                        confirm(
                            'This is the FINAL submission of marks. Continue?'
                        )
                    ) {

                        submitFinalButton.disabled =
                            true;

                        submitFinalButton.innerHTML =
                            'Submitting...';

                        HTMLFormElement
                            .prototype
                            .submit
                            .call(
                                finalSubmitForm
                            );
                    }

                    return;
                }


                Swal.fire({

                    icon: 'warning',

                    title: 'Final Marks Submission',

                    html:
                        '<div style="text-align:left">' +

                        '<b>This is the FINAL submission of marks.</b>' +

                        '<br><br>' +

                        'Please check all marks carefully.' +

                        '<br><br>' +

                        '<ul style="margin-top:8px;margin-left:20px;">' +

                        '<li>Marks will be locked.</li>' +

                        '<li>Teacher cannot modify the marks.</li>' +

                        '<li>Administrator intervention will be required for corrections.</li>' +

                        '</ul>' +

                        '</div>',

                    showCancelButton: true,

                    confirmButtonText:
                        'Submit Final Marks',

                    cancelButtonText:
                        'Cancel',

                    confirmButtonColor:
                        '#16a34a'

                }).then(function (result) {

                    if (!result.isConfirmed) {
                        return;
                    }


                    submitFinalButton.disabled =
                        true;

                    submitFinalButton.innerHTML =
                        'Submitting...';


                    /*
                     * Bypass normal submit event.
                     */

                    HTMLFormElement
                        .prototype
                        .submit
                        .call(
                            finalSubmitForm
                        );

                });

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    const yearSelect =
        document.getElementById(
            'academic_year_id'
        );

    const examSelect =
        document.getElementById(
            'exam_master_id'
        );

    const assignmentSelect =
        document.getElementById(
            'teacher_subject_allocation_id'
        );

    const filterForm =
        document.getElementById(
            'marksFilterForm'
        );


    if (
        yearSelect &&
        filterForm
    ) {

        yearSelect.addEventListener(
            'change',
            function () {

                if (assignmentSelect) {

                    assignmentSelect.innerHTML =
                        '<option value="">Select Exam First</option>';

                    assignmentSelect.disabled =
                        true;
                }

                filterForm.submit();
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXAM CHANGE
    |--------------------------------------------------------------------------
    */

    if (
        examSelect &&
        filterForm
    ) {

        examSelect.addEventListener(
            'change',
            function () {

                if (assignmentSelect) {

                    assignmentSelect.innerHTML =
                        '<option value="">Loading assignments...</option>';

                    assignmentSelect.disabled =
                        true;
                }

                filterForm.submit();
            }
        );
    }

});