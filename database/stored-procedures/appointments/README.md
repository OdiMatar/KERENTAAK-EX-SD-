# Afspraken stored procedures

Deze map beschrijft de CRUD-verdeling voor de afsprakenmodule.

- `create_appointment.sql`: create-regels, uitgevoerd door `sp_create_appointment`.
- `read_appointments.sql`: read-regels, uitgevoerd door `sp_get_customer_appointments`.
- `update_appointment.sql`: update-regels, uitgevoerd door `sp_update_appointment`.
- `delete_appointment.sql`: delete/annuleer-regels, uitgevoerd door `sp_cancel_appointment`.

Het uitvoerbare installatiebestand staat een map hoger:
`database/stored-procedures/appointments_procedures.sql`.

Gebruik geen Laravel seeders voor deze basisdata. De procedure
`sp_seed_appointment_basisdata` vult behandelingen, medewerkers en
specialisaties.
