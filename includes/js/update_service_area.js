document.addEventListener('DOMContentLoaded', function() {
    const serviceAreasContainer = document.getElementById('serviceAreasContainer');
    const addServiceAreaButton = document.getElementById('addServiceAreaButton');
    const deletedServiceAreasInput = document.getElementById('deletedServiceAreas');
    let serviceAreaCount = document.querySelectorAll('.service-area-block').length;
    let deletedServiceAreas = [];

    // Load states and areas from JSON
    let statesAndAreas = {};
    fetch('../data/postcode.json')
        .then(response => response.json())
        .then(data => {
            statesAndAreas = data.state;
            populateStateOptions();
            populateExistingServiceAreas();
        });

    function populateStateOptions() {
        const stateSelects = document.querySelectorAll('.state-select');
        stateSelects.forEach(select => {
            const currentState = select.getAttribute('data-state');
            select.innerHTML = '<option value="">Select State</option>'; // Ensure fresh list
            statesAndAreas.forEach(state => {
                const option = document.createElement('option');
                option.value = state.name;
                option.textContent = state.name;
                if (state.name === currentState) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        });
    }

    function populateAreaOptions(stateSelect) {
        const areaSelect = stateSelect.closest('.service-area-block').querySelector('.area-select');
        const currentArea = areaSelect.getAttribute('data-area');
        areaSelect.innerHTML = '<option value="">Select Area</option>'; // Reset area dropdown

        const selectedState = stateSelect.value;
        if (selectedState) {
            const state = statesAndAreas.find(s => s.name === selectedState);
            if (state) {
                state.city.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.name;
                    option.textContent = city.name;
                    if (city.name === currentArea) {
                        option.selected = true;
                    }
                    areaSelect.appendChild(option);
                });
            }
        }
    }

    function populateExistingServiceAreas() {
        const serviceAreaBlocks = document.querySelectorAll('.service-area-block');
        serviceAreaBlocks.forEach(block => {
            const stateSelect = block.querySelector('.state-select');
            const areaSelect = block.querySelector('.area-select');
            const state = stateSelect.getAttribute('data-state');
            const area = areaSelect.getAttribute('data-area');
            stateSelect.value = state;
            populateAreaOptions(stateSelect);
            areaSelect.value = area;
        });
    }

    addServiceAreaButton.addEventListener('click', function() {
        serviceAreaCount++;
        const serviceAreaBlock = document.createElement('div');
        serviceAreaBlock.classList.add('service-area-block');
        serviceAreaBlock.innerHTML = `
            <button type="button" class="delete-button" style="
                                            position: absolute;
                                            top: 1px;
                                            right: 2%;
                                            background-color: red;
                                            color: white;
                                            border: none;
                                            border-radius: 5px;
                                            width: 25px;
                                            height: 25px;
                                            cursor: pointer;
                                            padding: unset;"><b>X</b></button>
            <label>State:</label>
            <select name="new_service_areas[${serviceAreaCount}][state]" class="state-select" required>
                <option value="">Select State</option>
            </select>
            <label>Area:</label>
            <select name="new_service_areas[${serviceAreaCount}][area]" class="area-select" required>
                <option value="">Select Area</option>
            </select>
        `;
        serviceAreasContainer.appendChild(serviceAreaBlock);
        populateStateOptions();
        addDeleteButtonListener(serviceAreaBlock.querySelector('.delete-button'));
        addStateSelectListener(serviceAreaBlock.querySelector('.state-select'));
    });

    function addDeleteButtonListener(button) {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this service area?')) {
                const serviceAreaBlock = button.parentElement;
                const serviceAreaId = serviceAreaBlock.getAttribute('data-service-area-id');
                if (serviceAreaId) {
                    deletedServiceAreas.push(serviceAreaId);
                    deletedServiceAreasInput.value = deletedServiceAreas.join(',');
                }
                serviceAreaBlock.remove();
                formChanged = true;
            }
        });
    }

    function addStateSelectListener(select) {
        select.addEventListener('change', function() {
            populateAreaOptions(select);
        });
    }

    document.querySelectorAll('.delete-button').forEach(addDeleteButtonListener);
    document.querySelectorAll('.state-select').forEach(addStateSelectListener);

    let formChanged = false;
    const updateServiceAreaForm = document.getElementById('updateServiceAreaForm');

    updateServiceAreaForm.addEventListener('input', function() {
        formChanged = true;
    });

    updateServiceAreaForm.addEventListener('submit', function() {
        window.removeEventListener('beforeunload', beforeUnloadHandler);
    });

    function beforeUnloadHandler(e) {
        if (formChanged) {
            const confirmationMessage = 'You have unsaved changes. Are you sure you want to leave without saving?';
            e.returnValue = confirmationMessage;
            return confirmationMessage;
        }
    }

    window.addEventListener('beforeunload', beforeUnloadHandler);
});