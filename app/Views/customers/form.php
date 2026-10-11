<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="directory-header shell customer-header form-page-header">
    <div>
        <span class="eyebrow">01 / Customer directory</span>
        <h1><?= $customer === null ? 'Add a customer' : 'Edit customer' ?></h1>
        <p><?= $customer === null ? 'Create a customer account with accurate contact information.' : 'Update the contact details saved on this customer account.' ?></p>
    </div>
    <a class="button ghost" href="<?= site_url('customers') ?>">Back to customers</a>
</section>

<section class="shell form-panel">
    <?php if ($formErrors !== []): ?>
        <div class="form-alert" role="alert">
            <strong>Please review the highlighted fields.</strong>
            <p>Correct the information below and submit the form again.</p>
            <?php if (isset($formErrors['general'])): ?><p><?= esc($formErrors['general']) ?></p><?php endif; ?>
        </div>
    <?php endif; ?>

    <form action="<?= esc($formAction, 'attr') ?>" method="post" novalidate>
        <?= csrf_field() ?>
        <div class="form-field">
            <label for="full_name">Full name <span aria-hidden="true">*</span></label>
            <input id="full_name" name="full_name" type="text" maxlength="100" autocomplete="name" value="<?= esc(old('full_name', $customer['full_name'] ?? ''), 'attr') ?>" required aria-invalid="<?= isset($formErrors['full_name']) ? 'true' : 'false' ?>" aria-describedby="full-name-help<?= isset($formErrors['full_name']) ? ' full-name-error' : '' ?>">
            <small id="full-name-help">Enter the customer's name (up to 100 characters).</small>
            <?php if (isset($formErrors['full_name'])): ?><p class="field-error" id="full-name-error"><?= esc($formErrors['full_name']) ?></p><?php endif; ?>
        </div>

        <div class="form-field">
            <label for="email">Email address <span aria-hidden="true">*</span></label>
            <input id="email" name="email" type="email" maxlength="100" autocomplete="email" value="<?= esc(old('email', $customer['email'] ?? ''), 'attr') ?>" required aria-invalid="<?= isset($formErrors['email']) ? 'true' : 'false' ?>" aria-describedby="email-help<?= isset($formErrors['email']) ? ' email-error' : '' ?>">
            <small id="email-help">Use a valid email address for receipts and account contact.</small>
            <?php if (isset($formErrors['email'])): ?><p class="field-error" id="email-error"><?= esc($formErrors['email']) ?></p><?php endif; ?>
        </div>

        <div class="form-field">
            <label for="phone">Phone number <span class="optional-label">Optional</span></label>
            <input id="phone" name="phone" type="tel" maxlength="20" autocomplete="tel" value="<?= esc(old('phone', $customer['phone'] ?? ''), 'attr') ?>" aria-invalid="<?= isset($formErrors['phone']) ? 'true' : 'false' ?>" aria-describedby="phone-help<?= isset($formErrors['phone']) ? ' phone-error' : '' ?>">
            <small id="phone-help">Include the country code if useful; up to 20 characters.</small>
            <?php if (isset($formErrors['phone'])): ?><p class="field-error" id="phone-error"><?= esc($formErrors['phone']) ?></p><?php endif; ?>
        </div>

        <div class="form-actions">
            <a class="button secondary" href="<?= site_url('customers') ?>">Cancel</a>
            <button class="button primary" type="submit"><?= $customer === null ? 'Save customer' : 'Save changes' ?></button>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
