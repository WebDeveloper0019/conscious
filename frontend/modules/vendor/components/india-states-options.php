<?php
/** Frontend-only reusable options for every Indian state and union territory. */
$selectedState = $selectedState ?? '';
$indiaStates = [
    'Andaman and Nicobar Islands','Andhra Pradesh','Arunachal Pradesh','Assam','Bihar',
    'Chandigarh','Chhattisgarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Goa',
    'Gujarat','Haryana','Himachal Pradesh','Jammu and Kashmir','Jharkhand','Karnataka',
    'Kerala','Ladakh','Lakshadweep','Madhya Pradesh','Maharashtra','Manipur','Meghalaya',
    'Mizoram','Nagaland','Odisha','Puducherry','Punjab','Rajasthan','Sikkim','Tamil Nadu',
    'Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal'
];
foreach ($indiaStates as $indiaState): ?>
<option value="<?= htmlspecialchars($indiaState, ENT_QUOTES, 'UTF-8') ?>"<?= $selectedState === $indiaState ? ' selected' : '' ?>><?= htmlspecialchars($indiaState, ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; unset($selectedState, $indiaStates, $indiaState); ?>
