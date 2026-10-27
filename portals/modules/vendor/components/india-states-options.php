<?php
/** Shared server-side list for all Indian states and union territories. */
$selectedState = $selectedState ?? '';
require_once __DIR__.'/../../../../api/lib/india.php';
$indiaStates = india_states_and_uts();
foreach ($indiaStates as $indiaState): ?>
<option value="<?= htmlspecialchars($indiaState, ENT_QUOTES, 'UTF-8') ?>"<?= $selectedState === $indiaState ? ' selected' : '' ?>><?= htmlspecialchars($indiaState, ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; unset($selectedState, $indiaStates, $indiaState); ?>
