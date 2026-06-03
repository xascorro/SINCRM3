select clubes.nombre as Club, federaciones.nombre_corto as Federación, count(rutinas.id) as 'Equipos/Combos', 10 as Precio, count(rutinas.id)*10 as Total
FROM rutinas, clubes, federaciones
where
rutinas.orden >=0 and
federacion=1 and
id_club=clubes.id and
federacion=federaciones.id and
rutinas.id_competicion = 52 and
rutinas.id_fase in (select id from fases where id_modalidad=3 or id_modalidad=4 or id_modalidad=7 or id_modalidad=9 or id_modalidad=10)
group by clubes.nombre_corto order by clubes.id;
;

select clubes.nombre as Club, federaciones.nombre_corto as Federación, count(rutinas.id) as 'Solos/Dúos', 2.5 as 'Precio S/D', count(rutinas.id)*2.5 as 'Total'
FROM rutinas, clubes, federaciones
where
rutinas.orden >=0 and
federacion=1 and
id_club=clubes.id and
federacion=federaciones.id and
rutinas.id_competicion = 52 and
rutinas.id_fase in (select id from fases where id_modalidad=1 or id_modalidad=2 or id_modalidad=5 or id_modalidad=6)
group by clubes.nombre_corto order by clubes.id;

select distinct clubes.nombre as Club, federaciones.nombre_corto as Federación,  count(distinct(concat(nadadoras.nombre, ' ', nadadoras.apellidos)))  as 'Participantes', 20 as 'Precio p', count('Participantes')*20 as 'Total'
FROM rutinas, nadadoras, rutinas_participantes, clubes, federaciones
where
rutinas.orden >=0 and
rutinas.id = rutinas_participantes.id_rutina and
rutinas_participantes.id_nadadora = nadadoras.id and
federacion > 1 and
id_club=clubes.id and
federacion=federaciones.id and
rutinas.id_competicion = 52
group by clubes.nombre_corto;
