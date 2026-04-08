<div class="dashboard-shell">
<section class="dashboard-command grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
    <div class="glass-panel p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-plum/45"><?= htmlspecialchars((string) $eyebrow, ENT_QUOTES, 'UTF-8') ?></p>
        <h1 class="mt-3 text-4xl font-semibold"><?= htmlspecialchars((string) $heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="mt-4 text-plum/70">
            <?= htmlspecialchars((string) $description, ENT_QUOTES, 'UTF-8') ?>
        </p>
        <div class="role-tabs mt-6">
            <?php foreach ($tabs as $tab): ?>
                <span class="role-tab"><?= htmlspecialchars((string) $tab, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endforeach; ?>
        </div>
        <div class="dashboard-pulse-grid mt-6">
            <span class="dashboard-pulse"></span>
            <span class="dashboard-pulse"></span>
            <span class="dashboard-pulse"></span>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <?php foreach ($metrics as $metric): ?>
            <div class="metric-tile min-h-[140px] justify-between">
                <div class="metric-tile__label"><?= htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="metric-tile__value"><?= htmlspecialchars($metric['value'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="mt-8 grid gap-6 lg:grid-cols-2">
    <?php foreach ($panels as $panel): ?>
        <article class="glass-panel p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-plum/45"><?= htmlspecialchars((string) $panel['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
            <h2 class="mt-3 text-2xl font-semibold"><?= htmlspecialchars((string) $panel['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="mt-4 text-plum/70">
                <?= htmlspecialchars((string) $panel['body'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </article>
    <?php endforeach; ?>
</section>

<?php if (in_array($role, ['OWNER', 'ADMIN'], true)): ?>
    <?php if ($role === 'ADMIN'): ?>
        <section class="mt-8">
            <div>
                <p class="text-sm uppercase tracking-[0.25em] text-plum/45">User management</p>
                <h2 class="mt-2 text-3xl font-semibold">Manage customers and owners</h2>
            </div>
            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <article class="glass-panel p-6">
                    <h3 class="text-2xl font-semibold">Customers</h3>
                    <div class="mt-4 grid gap-4">
                        <?php foreach ($customerUsers as $user): ?>
                            <div class="booking-card">
                                <div>
                                    <div class="ranking-item__label"><?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="ranking-item__meta"><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars((string) ($user['phone_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="ranking-item__meta">Status: <?= ((int) ($user['is_active'] ?? 1) === 1) ? 'Active' : 'Inactive' ?></div>
                                </div>
                                <div class="admin-actions">
                                    <form method="post" action="<?= htmlspecialchars($url('dashboard/users/change-role'), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <select name="role">
                                            <?php foreach (['CUSTOMER', 'OWNER', 'ADMIN'] as $userRole): ?>
                                                <option value="<?= $userRole ?>" <?= (($user['role'] ?? '') === $userRole) ? 'selected' : '' ?>><?= $userRole ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="hero-link planner-submit">Change role</button>
                                    </form>
                                    <form method="post" action="<?= htmlspecialchars($url('dashboard/users/toggle-status'), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="is_active" value="<?= ((int) ($user['is_active'] ?? 1) === 1) ? '0' : '1' ?>">
                                        <button type="submit" class="hero-link hero-link--secondary planner-submit">
                                            <?= ((int) ($user['is_active'] ?? 1) === 1) ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="glass-panel p-6">
                    <h3 class="text-2xl font-semibold">Owners</h3>
                    <div class="mt-4 grid gap-4">
                        <?php foreach ($ownerUsers as $user): ?>
                            <div class="booking-card">
                                <div>
                                    <div class="ranking-item__label"><?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="ranking-item__meta"><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars((string) ($user['phone_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="ranking-item__meta">Status: <?= ((int) ($user['is_active'] ?? 1) === 1) ? 'Active' : 'Inactive' ?></div>
                                </div>
                                <div class="admin-actions">
                                    <form method="post" action="<?= htmlspecialchars($url('dashboard/users/change-role'), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <select name="role">
                                            <?php foreach (['CUSTOMER', 'OWNER', 'ADMIN'] as $userRole): ?>
                                                <option value="<?= $userRole ?>" <?= (($user['role'] ?? '') === $userRole) ? 'selected' : '' ?>><?= $userRole ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="hero-link planner-submit">Change role</button>
                                    </form>
                                    <form method="post" action="<?= htmlspecialchars($url('dashboard/users/toggle-status'), ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="is_active" value="<?= ((int) ($user['is_active'] ?? 1) === 1) ? '0' : '1' ?>">
                                        <button type="submit" class="hero-link hero-link--secondary planner-submit">
                                            <?= ((int) ($user['is_active'] ?? 1) === 1) ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </div>
        </section>
    <?php endif; ?>

    <section class="mt-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.25em] text-plum/45"><?= $role === 'OWNER' ? 'My venue' : 'Venue management' ?></p>
                <h2 class="mt-2 text-3xl font-semibold"><?= $role === 'OWNER' ? 'Your assigned venue details' : 'Edit venue details directly' ?></h2>
            </div>
        </div>
        <div class="mt-6 grid gap-6">
            <?php if (empty($manageableVenues)): ?>
                <article class="glass-panel p-6">
                    <p class="text-plum/70">No venues are assigned to this dashboard yet.</p>
                </article>
            <?php else: ?>
                <?php foreach ($manageableVenues as $venue): ?>
                    <article class="glass-panel p-6">
                        <?php if ($role === 'ADMIN'): ?>
                            <form class="planner-form" method="post" action="<?= htmlspecialchars($url('dashboard/venues/update'), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="venue_id" value="<?= htmlspecialchars((string) $venue['venueId'], ENT_QUOTES, 'UTF-8') ?>">
                                <label class="planner-field">
                                    <span>Venue name</span>
                                    <input type="text" name="name" value="<?= htmlspecialchars((string) $venue['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </label>
                                <label class="planner-field">
                                    <span>Neighborhood</span>
                                    <input type="text" name="neighborhood" value="<?= htmlspecialchars((string) $venue['neighborhood'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </label>
                                <label class="planner-field">
                                    <span>Category</span>
                                    <input type="text" name="event_category" value="<?= htmlspecialchars((string) $venue['eventType'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </label>
                                <label class="planner-field">
                                    <span>Base price</span>
                                    <input type="number" step="0.01" name="base_price" value="<?= htmlspecialchars((string) $venue['totalAmount'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </label>
                                <label class="planner-field">
                                    <span>Capacity range</span>
                                    <input type="text" name="capacity_range" value="<?= htmlspecialchars((string) $venue['capacity'], ENT_QUOTES, 'UTF-8') ?>" required>
                                </label>
                                <label class="planner-field planner-field--wide">
                                    <span>Description</span>
                                    <textarea name="description" rows="4"><?= htmlspecialchars((string) $venue['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                </label>
                                <button type="submit" class="hero-link planner-submit">Save venue</button>
                            </form>
                        <?php else: ?>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="info-chip">
                                    <span class="text-plum/55">Venue</span>
                                    <strong><?= htmlspecialchars((string) $venue['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </div>
                                <div class="info-chip">
                                    <span class="text-plum/55">Category</span>
                                    <strong><?= htmlspecialchars((string) $venue['eventType'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </div>
                                <div class="info-chip">
                                    <span class="text-plum/55">Neighborhood</span>
                                    <strong><?= htmlspecialchars((string) $venue['neighborhood'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </div>
                                <div class="info-chip">
                                    <span class="text-plum/55">Owner email</span>
                                    <strong><?= htmlspecialchars((string) ($venue['ownerEmail'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                </div>
                                <div class="info-chip">
                                    <span class="text-plum/55">Base price</span>
                                    <strong><?= htmlspecialchars((string) $venue['price'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </div>
                                <div class="info-chip">
                                    <span class="text-plum/55">Capacity</span>
                                    <strong><?= htmlspecialchars((string) $venue['capacity'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </div>
                            </div>
                            <p class="mt-4 text-plum/70"><?= htmlspecialchars((string) $venue['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($role === 'ADMIN'): ?>
    <section class="mt-8">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-plum/45">Slot control</p>
            <h2 class="mt-2 text-3xl font-semibold">Update availability windows and status</h2>
        </div>
        <div class="mt-6 grid gap-6">
            <?php if (empty($manageableSlots)): ?>
                <article class="glass-panel p-6">
                    <p class="text-plum/70">No slots are available to manage yet.</p>
                </article>
            <?php else: ?>
                <?php foreach ($manageableSlots as $slot): ?>
                    <article class="glass-panel p-6">
                        <form class="planner-form" method="post" action="<?= htmlspecialchars($url('dashboard/slots/update'), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="slot_id" value="<?= htmlspecialchars((string) $slot['id'], ENT_QUOTES, 'UTF-8') ?>">
                            <label class="planner-field">
                                <span>Venue</span>
                                <input type="text" value="<?= htmlspecialchars((string) $slot['venue_name'], ENT_QUOTES, 'UTF-8') ?>" disabled>
                            </label>
                            <label class="planner-field">
                                <span>Status</span>
                                <select name="status">
                                    <?php foreach (['AVAILABLE', 'HELD', 'BOOKED', 'EXPIRED'] as $status): ?>
                                        <option value="<?= $status ?>" <?= (($slot['status'] ?? '') === $status) ? 'selected' : '' ?>><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="planner-field">
                                <span>Slot start</span>
                                <input type="datetime-local" name="slot_start" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime((string) $slot['slot_start'])), ENT_QUOTES, 'UTF-8') ?>" required>
                            </label>
                            <label class="planner-field">
                                <span>Slot end</span>
                                <input type="datetime-local" name="slot_end" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime((string) $slot['slot_end'])), ENT_QUOTES, 'UTF-8') ?>" required>
                            </label>
                            <button type="submit" class="hero-link planner-submit">Save slot</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="mt-8">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-plum/45">Booking review</p>
            <h2 class="mt-2 text-3xl font-semibold">Accept or reject venue bookings</h2>
        </div>
        <div class="mt-6 grid gap-6">
            <?php if (empty($manageableBookings)): ?>
                <article class="glass-panel p-6">
                    <p class="text-plum/70">No bookings are waiting for review right now.</p>
                </article>
            <?php else: ?>
                <?php foreach ($manageableBookings as $booking): ?>
                    <article class="glass-panel p-6">
                        <div class="grid gap-4 lg:grid-cols-4">
                            <div class="info-chip">
                                <span class="text-plum/55">Reference</span>
                                <strong><?= htmlspecialchars((string) $booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="info-chip">
                                <span class="text-plum/55">Customer</span>
                                <strong><?= htmlspecialchars((string) $booking['customer_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="info-chip">
                                <span class="text-plum/55">Venue</span>
                                <strong><?= htmlspecialchars((string) $booking['venue_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="info-chip">
                                <span class="text-plum/55">Status</span>
                                <strong><?= htmlspecialchars((string) $booking['booking_status'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                        </div>
                        <p class="mt-4 text-plum/70">
                            Slot: <?= htmlspecialchars((string) $booking['slot_start'], ENT_QUOTES, 'UTF-8') ?> to <?= htmlspecialchars((string) $booking['slot_end'], ENT_QUOTES, 'UTF-8') ?> |
                            Payment: <?= htmlspecialchars((string) $booking['payment_status'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <div class="card-actions mt-6">
                            <form method="post" action="<?= htmlspecialchars($url('dashboard/bookings/review'), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="booking_reference" value="<?= htmlspecialchars((string) $booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="decision" value="APPROVED">
                                <button type="submit" class="hero-link planner-submit">Approve booking</button>
                            </form>
                            <form method="post" action="<?= htmlspecialchars($url('dashboard/bookings/review'), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="booking_reference" value="<?= htmlspecialchars((string) $booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="decision" value="REJECTED">
                                <button type="submit" class="hero-link hero-link--secondary planner-submit">Reject booking</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-2">
        <article class="glass-panel p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-plum/45">Upcoming bookings</p>
            <h2 class="mt-2 text-3xl font-semibold">Future reservation calendar</h2>
            <div class="mt-6 grid gap-4">
                <?php if (empty($upcomingBookings ?? [])): ?>
                    <div class="booking-card">
                        <div class="ranking-item__meta">No upcoming bookings are scheduled right now.</div>
                    </div>
                <?php else: ?>
                    <?php foreach (($upcomingBookings ?? []) as $booking): ?>
                        <div class="booking-card">
                            <div>
                                <div class="ranking-item__label"><?= htmlspecialchars((string) $booking['venue_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="ranking-item__meta"><?= htmlspecialchars((string) $booking['customer_name'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars((string) $booking['slot_start'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="ranking-item__meta">Status: <?= htmlspecialchars((string) $booking['booking_status'], ENT_QUOTES, 'UTF-8') ?> | Payment: <?= htmlspecialchars((string) $booking['payment_status'], ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                            <strong><?= htmlspecialchars((string) $booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="glass-panel p-6">
            <p class="text-sm uppercase tracking-[0.25em] text-plum/45">Past bookings</p>
            <h2 class="mt-2 text-3xl font-semibold">Completed booking history</h2>
            <div class="mt-6 grid gap-4">
                <?php if (empty($pastBookings ?? [])): ?>
                    <div class="booking-card">
                        <div class="ranking-item__meta">No past bookings are available yet.</div>
                    </div>
                <?php else: ?>
                    <?php foreach (($pastBookings ?? []) as $booking): ?>
                        <div class="booking-card">
                            <div>
                                <div class="ranking-item__label"><?= htmlspecialchars((string) $booking['venue_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="ranking-item__meta"><?= htmlspecialchars((string) $booking['customer_name'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars((string) $booking['slot_start'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="ranking-item__meta">Status: <?= htmlspecialchars((string) $booking['booking_status'], ENT_QUOTES, 'UTF-8') ?> | Payment: <?= htmlspecialchars((string) $booking['payment_status'], ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                            <strong><?= htmlspecialchars((string) $booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    </section>
<?php else: ?>
    <section class="mt-8 glass-panel p-8">
        <p class="text-sm uppercase tracking-[0.25em] text-plum/45">Customer access</p>
        <h2 class="mt-2 text-3xl font-semibold">Your dashboard is focused on discovery and booking tracking</h2>
        <p class="mt-4 text-plum/70">
            Customers can browse venues, plan events, and track deposits. Venue editing, slot management, and booking approvals are available only to owners and admins.
        </p>
        <div class="hero-actions mt-6">
            <a class="hero-link" href="<?= htmlspecialchars($url('venues'), ENT_QUOTES, 'UTF-8') ?>">Browse venues</a>
            <a class="hero-link hero-link--secondary" href="<?= htmlspecialchars($url('bookings'), ENT_QUOTES, 'UTF-8') ?>">My bookings</a>
        </div>
    </section>
<?php endif; ?>
</div>
