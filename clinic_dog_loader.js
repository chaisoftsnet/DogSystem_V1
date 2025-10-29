// clinic_dog_loader.js

document.addEventListener("DOMContentLoaded", function () {
    const clinicSelect = document.getElementById("clinic_id");
    const dogSelect = document.getElementById("dog_id");

    if (clinicSelect && dogSelect) {
        clinicSelect.addEventListener("change", function () {
            let clinic_id = this.value;
            if (clinic_id) {
                fetch("get_dogs.php?clinic_id=" + clinic_id)
                    .then(res => res.json())
                    .then(data => {
                        dogSelect.innerHTML = '<option value="">-- เลือกสุนัข --</option>';
                        data.forEach(dog => {
                            let option = document.createElement("option");
                            option.value = dog.dog_id;
                            option.textContent = dog.dog_name;
                            dogSelect.appendChild(option);
                        });
                    })
                    .catch(err => console.error("Error loading dogs:", err));
            } else {
                dogSelect.innerHTML = '<option value="">-- เลือกสุนัข --</option>';
            }
        });
    }
});
