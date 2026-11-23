/*
 * Dashboard – the SAME endpoint (GET /dashboard) returns different data for admins and students.
 */
const DashboardPage = {
    init: function () {
        const user = Auth.getUser();
        $("#dashboard-greeting").text("Welcome back, " + user.first_name + " 👋");

        RestClient.get("dashboard", function (stats) {
            if (stats.role === Constants.ADMIN_ROLE) {
                $("#dashboard-subtitle").text("Here is what is happening on campus this week.");
                DashboardPage.renderStats([
                    { label: "Upcoming events", value: stats.upcoming_events, icon: "bi-calendar-event", color: "indigo" },
                    { label: "Students", value: stats.students, icon: "bi-people", color: "green" },
                    { label: "Upcoming registrations", value: stats.upcoming_registrations, icon: "bi-ticket-perforated", color: "amber" },
                    { label: "Attended", value: stats.attended, icon: "bi-person-check", color: "rose" }
                ]);
                $("#starting-soon-column").removeClass("col-lg-12").addClass("col-lg-4");
                $("#starting-soon-title").text("Starting soon (next 7 days)");
                DashboardPage.renderChart(stats.registrations_per_category);
                DashboardPage.renderEventsSoon(stats.starting_soon);
            } else {
                $("#dashboard-subtitle").text("Your campus week at a glance.");
                DashboardPage.renderStats([
                    { label: "Upcoming events on campus", value: stats.upcoming_events, icon: "bi-calendar-event", color: "indigo" },
                    { label: "My upcoming registrations (max " + stats.max_upcoming_registrations + ")", value: stats.upcoming_registrations, icon: "bi-ticket-perforated", color: "green" },
                    { label: "Events I attended", value: stats.attended, icon: "bi-person-check", color: "amber" },
                    { label: "My reviews", value: stats.reviews, icon: "bi-star", color: "rose" }
                ]);
                $("#starting-soon-column").removeClass("col-lg-4").addClass("col-lg-12");
                $("#starting-soon-title").text("My events in the next 7 days");
                DashboardPage.renderMyEventsSoon(stats.starting_soon);
            }
        });
    },

    renderStats: function (cards) {
        const html = cards.map(card => `
            <div class="col-6 col-xl-3">
                <div class="card stat-card"><div class="card-body">
                    <div class="stat-icon bg-soft-${card.color}"><i class="bi ${card.icon}"></i></div>
                    <div><div class="stat-value">${card.value}</div><div class="stat-label">${card.label}</div></div>
                </div></div>
            </div>`).join("");
        $("#dashboard-stats").html(html);
    },

    /** Admin: events (with free seats) starting in the next 7 days. */
    renderEventsSoon: function (events) {
        if (events.length === 0) {
            $("#starting-soon-list").html('<li class="list-group-item text-muted py-4 text-center">No events in the next 7 days.</li>');
            return;
        }
        $("#starting-soon-list").html(events.map(event => `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">${event.title}</div>
                    <small class="text-muted">${event.location} · ${Number(event.available_seats) > 0 ? event.available_seats + " seats left" : "sold out"}</small>
                </div>
                ${Utils.startsBadge(event.starts_at)}
            </li>`).join(""));
    },

    /** Student: MY registrations for events in the next 7 days. */
    renderMyEventsSoon: function (registrations) {
        if (registrations.length === 0) {
            $("#starting-soon-list").html('<li class="list-group-item text-muted py-4 text-center">Nothing planned for the next days – <a href="#events">find an event</a> 🎉</li>');
            return;
        }
        $("#starting-soon-list").html(registrations.map(registration => `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">${registration.event_title}</div>
                    <small class="text-muted">${registration.event_location} · ${Utils.formatDateTime(registration.event_starts_at)}</small>
                </div>
                ${Utils.startsBadge(registration.event_starts_at)}
            </li>`).join(""));
    },

    renderChart: function (rows) {
        Highcharts.chart("registrations-chart", {
            chart: { type: "column", backgroundColor: "transparent", style: { fontFamily: "Inter" } },
            title: { text: null },
            credits: { enabled: false },
            legend: { enabled: false },
            xAxis: { categories: rows.map(row => row.category) },
            yAxis: { title: { text: "Registrations" }, allowDecimals: false },
            series: [{ name: "Registrations", data: rows.map(row => Number(row.total)), color: "#7c3aed", borderRadius: 6 }]
        });
    }
};
