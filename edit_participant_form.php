<?php
// This is included inside edit_participant.php.
// Variables $rider should be set by the parent script.
if (!isset($rider) || !$rider) {
    echo "<div class='alert-box alert-danger'><p>Rider data is missing or could not be loaded.</p></div>";
    return;
}
?>
<form action="edit_participant.php" method="POST">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($rider['id']); ?>">

    <div class="form-group">
        <label for="firstname">Rider First Name</label>
        <input type="text" id="firstname" name="firstname" class="form-control" disabled value="<?php echo htmlspecialchars($rider['firstname']); ?>">
        <span style="font-size: 0.76rem; color: var(--asphalt);">First name cannot be changed here.</span>
    </div>

    <div class="form-group">
        <label for="surname">Rider Surname</label>
        <input type="text" id="surname" name="surname" class="form-control" disabled value="<?php echo htmlspecialchars($rider['surname']); ?>">
        <span style="font-size: 0.76rem; color: var(--asphalt);">Surname cannot be changed here.</span>
    </div>

    <div class="form-group">
        <label for="power_output">Power Output (Watts)</label>
        <input type="number" step="any" id="power_output" name="power_output" class="form-control" placeholder="e.g. 250" value="<?php echo htmlspecialchars($rider['power_output'] ?? ''); ?>">
    </div>

    <div class="form-group">
        <label for="distance_travelled">Distance Travelled (KM)</label>
        <input type="number" step="any" id="distance_travelled" name="distance_travelled" class="form-control" placeholder="e.g. 42.5" value="<?php echo htmlspecialchars($rider['distance'] ?? $rider['distance_travelled'] ?? ''); ?>">
    </div>

    <div class="form-actions">
        <a href="view_participants_edit_delete.php" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">Update Rider Stats</button>
    </div>
</form>