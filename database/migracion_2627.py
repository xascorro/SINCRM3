#!/usr/bin/env python3
"""
Generador del script SQL para la migración de la normativa World Aquatics / RFEN 2026-2027 (Versión 2627)
para las tablas de Dificultad / Coach Cards:
- dificultad_basemark
- dificultad_tre
- dificultad_hibridos
- dificultad_acrobacias
- dificultad_acropair
"""

import os

def generate_sql():
    sql = []
    sql.append("-- ====================================================================")
    sql.append("-- NORMATIVA WORLD AQUATICS / RFEN TEMPORADA 2026-2027 (VERSIÓN 2627)")
    sql.append("-- ====================================================================\n")

    # 0. Ajustes de longitud de columnas seguras
    sql.append("ALTER TABLE dificultad_tre MODIFY COLUMN codigo varchar(20) NULL;")
    sql.append("ALTER TABLE dificultad_tre MODIFY COLUMN agrupar varchar(30) NULL;")
    sql.append("ALTER TABLE dificultad_tre MODIFY COLUMN descripcion varchar(300) NULL;")
    sql.append("ALTER TABLE dificultad_acropair MODIFY COLUMN codigo varchar(25) NULL;")
    sql.append("ALTER TABLE dificultad_acropair MODIFY COLUMN descripcion varchar(500) NULL;")
    sql.append("ALTER TABLE dificultad_acrobacias MODIFY COLUMN codigo varchar(25) NULL;")
    sql.append("ALTER TABLE dificultad_acrobacias MODIFY COLUMN descripcion varchar(500) NULL;")
    sql.append("ALTER TABLE dificultad_hibridos MODIFY COLUMN descripcion varchar(300) NULL;")
    sql.append("ALTER TABLE dificultad_basemark MODIFY COLUMN agrupar varchar(30) NULL;\n")

    # 1. Limpieza de registros previos de 2627
    for tbl in ['dificultad_hibridos', 'dificultad_tre', 'dificultad_basemark', 'dificultad_acrobacias', 'dificultad_acropair']:
        sql.append(f"DELETE FROM {tbl} WHERE version = '2627';")
    sql.append("")

    # 2. DIFICULTAD_BASEMARK
    sql.append("-- --------------------------------------------------------------------")
    sql.append("-- 1. DIFICULTAD_BASEMARK (2627)")
    sql.append("-- --------------------------------------------------------------------")
    basemarks = [
        ("HYBRID", 0.50, "", "Base Mark Híbrido"),
        ("TRE", 0.00, "", "Base Mark Elemento Técnico"),
        ("ACROPAIR", 0.10, "", "Base Mark Acrobacia en Pareja"),
        ("ACRO-A", 0.50, "", "Base Mark Acrobacia Grupo A"),
        ("ACRO-B", 0.50, "", "Base Mark Acrobacia Grupo B"),
        ("ACRO-C", 0.50, "", "Base Mark Acrobacia Grupo C"),
        ("ACRO-P", 0.50, "", "Base Mark Acrobacia Grupo P"),
        ("Grupo-A", 0.50, "", "Base Mark Acrobacia Grupo A"),
        ("Grupo-B", 0.50, "", "Base Mark Acrobacia Grupo B"),
        ("Grupo-P", 0.50, "", "Base Mark Acrobacia Grupo P"),
        ("Grupo-C", 0.50, "", "Base Mark Acrobacia Grupo C")
    ]
    for cod, val, agr, desc in basemarks:
        sql.append(f"INSERT INTO dificultad_basemark (codigo, valor, agrupar, descripcion, version, temporada) VALUES ('{cod}', {val:.2f}, '{agr}', '{desc}', '2627', '2026-2027');")
    sql.append("")

    # 3. DIFICULTAD_TRE
    sql.append("-- --------------------------------------------------------------------")
    sql.append("-- 2. DIFICULTAD_TRE (2627)")
    sql.append("-- --------------------------------------------------------------------")
    tres = [
        # Solo Técnico Femenino
        ("S-TRE1a", 2.70, "Solo Femenino", "Barracuda Tirabuzón Continuo 720º"),
        ("S-TRE1b", 2.00, "Solo Femenino", "Barracuda Tirabuzón Continuo 360º"),
        ("S-TRE2",  3.50, "Solo Femenino", "Clepsydra"),
        ("S-TRE3a", 3.10, "Solo Femenino", "Marsopa Dos Direcciones Tirabuzón Ascendente 720º"),
        ("S-TRE3b", 2.60, "Solo Femenino", "Marsopa Dos Direcciones Tirabuzón Ascendente 360º"),
        ("S-TRE4a", 3.60, "Solo Femenino", "Pacific Cerrando 360º"),
        ("S-TRE4b", 3.50, "Solo Femenino", "Pacific"),
        ("S-TRE5a", 2.50, "Solo Femenino", "Rocket Espagat Tirabuzón 720º (360º juntar + 360º)"),
        ("S-TRE5b", 2.20, "Solo Femenino", "Rocket Espagat Tirabuzón 360º (180º juntar + 180º)"),

        # Solo Técnico Masculino
        ("SM-TRE1a", 2.70, "Solo Masculino", "Barracuda Tirabuzón Continuo 720º"),
        ("SM-TRE1b", 2.00, "Solo Masculino", "Barracuda Tirabuzón Continuo 360º"),
        ("SM-TRE2a", 2.40, "Solo Masculino", "Poseidón Giro Completo"),
        ("SM-TRE2b", 1.90, "Solo Masculino", "Poseidón Medio Giro"),
        ("SM-TRE3a", 3.20, "Solo Masculino", "Tritón Giro Completo"),
        ("SM-TRE3b", 3.00, "Solo Masculino", "Tritón Medio Giro"),
        ("SM-TRE4a", 2.20, "Solo Masculino", "Sylpha Tirabuzón 360º"),
        ("SM-TRE4b", 2.10, "Solo Masculino", "Sylpha Tirabuzón 180º"),
        ("SM-TRE5a", 2.60, "Solo Masculino", "Atlas Tirabuzón Continuo 1080º"),
        ("SM-TRE5b", 1.90, "Solo Masculino", "Atlas Tirabuzón Continuo 360º"),

        # Dúo Técnico Femenino
        ("D-TRE1a", 2.30, "Dúo Femenino", "Barracuda Rodilla Doblada Twirl Tirabuzón 360º"),
        ("D-TRE1b", 2.10, "Dúo Femenino", "Barracuda Rodilla Doblada Twirl Tirabuzón 180º"),
        ("D-TRE2a", 2.50, "Dúo Femenino", "Rocket Espagat Catch Tirabuzón 360º"),
        ("D-TRE2b", 2.40, "Dúo Femenino", "Rocket Espagat Catch Tirabuzón 180º"),
        ("D-TRE3",  3.60, "Dúo Femenino", "Sundial"),
        ("D-TRE4a", 3.40, "Dúo Femenino", "Swan Medio Giro"),
        ("D-TRE4b", 2.90, "Dúo Femenino", "Swan"),
        ("D-TRE5a", 2.70, "Dúo Femenino", "Mercury Tirabuzón Continuo 720º"),
        ("D-TRE5b", 2.50, "Dúo Femenino", "Mercury Tirabuzón Continuo 360º"),

        # Dúo Mixto Técnico
        ("M-TRE1a", 3.10, "Dúo Mixto", "Albatros Helicóptero Tirabuzón Continuo 720º"),
        ("M-TRE1b", 2.90, "Dúo Mixto", "Albatros Helicóptero Tirabuzón Continuo 360º"),
        ("M-TRE2a", 2.30, "Dúo Mixto", "Osprey Rotación 180º"),
        ("M-TRE2b", 2.20, "Dúo Mixto", "Osprey"),
        ("M-TRE3a", 3.20, "Dúo Mixto", "Seraphin Twirl"),
        ("M-TRE3b", 2.90, "Dúo Mixto", "Seraphin"),
        ("M-TRE4a", 3.00, "Dúo Mixto", "Fouetté Tirabuzón Continuo 720º"),
        ("M-TRE4b", 2.80, "Dúo Mixto", "Fouetté Tirabuzón Continuo 360º"),
        ("M-TRE5a", 2.60, "Dúo Mixto", "Ciclón Giro Completo"),
        ("M-TRE5b", 2.40, "Dúo Mixto", "Ciclón Medio Giro"),

        # Equipo Técnico
        ("T-TRE1a", 2.90, "Equipo", "Fouetté Tirabuzón Continuo 720º"),
        ("T-TRE1b", 2.40, "Equipo", "Fouetté Tirabuzón Continuo 360º"),
        ("T-TRE2a", 2.80, "Equipo", "Ciclón Giro Completo"),
        ("T-TRE2b", 2.60, "Equipo", "Ciclón Medio Giro"),
        ("T-TRE3a", 2.80, "Equipo", "Barracuda Tirabuzón 360º"),
        ("T-TRE3b", 2.30, "Equipo", "Barracuda Tirabuzón 180º"),
        ("T-TRE4a", 2.90, "Equipo", "Rocket Espagat Tirabuzón 360º"),
        ("T-TRE4b", 2.70, "Equipo", "Rocket Espagat Tirabuzón 180º"),
        ("T-TRE5a", 2.60, "Equipo", "Elaris Cambio de Formación Tirabuzón Combinado 720º"),
        ("T-TRE5b", 2.20, "Equipo", "Elaris Cambio de Formación Tirabuzón Combinado 360º"),
    ]
    for cod, val, agr, desc in tres:
        sql.append(f"INSERT INTO dificultad_tre (codigo, valor, agrupar, descripcion, version, temporada) VALUES ('{cod}', {val:.2f}, '{agr}', '{desc}', '2627', '2026-2027');")
    sql.append("")

    # 4. DIFICULTAD_HIBRIDOS
    sql.append("-- --------------------------------------------------------------------")
    sql.append("-- 3. DIFICULTAD_HIBRIDOS (2627)")
    sql.append("-- --------------------------------------------------------------------")
    hibridos_base = []

    # EMPUJES
    empujes = [
        ("TB",  0.300, "Empuje con una o dos piernas seguido de caída en superficie (crashing)"),
        ("T1",  0.450, "Empuje con una pierna seguido de descenso vertical"),
        ("T2a", 0.500, "Empuje con una pierna seguido de tirabuzón 180º"),
        ("T2b", 0.500, "Empuje con una pierna seguido de un Twirl 180º y caída (crash)"),
        ("T3a", 0.650, "Empuje con descenso vertical"),
        ("T3b", 0.650, "Empuje con una pierna seguido de tirabuzón 360º"),
        ("T3c", 0.650, "Empuje con una pierna seguido de un Twirl 180º y un descenso vertical"),
        ("T3d", 0.650, "Empuje con flexibilidad seguido de crash"),
        ("T4a", 0.850, "Empuje con una pierna seguido de tirabuzón 720º"),
        ("T4b", 0.850, "Empuje con flexibilidad y descenso"),
        ("T4c", 0.850, "Empuje seguido de tirabuzón 180º"),
        ("T4d", 0.850, "Pez Volador (con descenso)"),
        ("T4e", 0.850, "Empuje seguido de Twirl 180º en PV con crash"),
        ("T5a", 0.900, "Empuje seguido de Tirabuzón 360º"),
        ("T5b", 0.900, "Empuje seguido de Twirl 180º en PV y descenso"),
        ("T5c", 0.900, "Empuje con flexibilidad seguido de Tirabuzón 180º"),
        ("T5d", 0.900, "Pez Volador Tirabuzón 180º o Empuje Cola de Pez Helicóptero Tirabuzón 180º"),
        ("T5e", 0.900, "Empuje de una pierna con Twirl 180º seguido de Tirabuzón 360º"),
        ("T6a", 1.200, "Empuje con flexibilidad seguido de Tirabuzón 360º"),
        ("T6b", 1.200, "Empuje con Twirl 180º en PV seguido de Tirabuzón 360º"),
        ("T6c", 1.200, "Pez Volador 360º o Empuje Cola de Pez Helicóptero Tirabuzón 360º"),
        ("T7",  1.600, "Empuje seguido de Tirabuzón 720º"),
        ("T8",  1.900, "Empuje con flexibilidad seguido de Tirabuzón 720º"),
        ("T9",  2.000, "Empuje altura cintura (8.5+) con parada 1s+ en PV rodillas+"),
        ("T10", 2.300, "Empuje altura cintura (8.5+) seguido de Tirabuzón 1080º o más")
    ]
    for cod, val, desc in empujes:
        hibridos_base.append(("Empujes", "", cod, val, desc))

    # TIRABUZONES
    tirabuzones = [
        ("SB",   0.150, "Tirabuzón 180º (una o dos piernas)"),
        ("SCB",  0.400, "Combinado 180º (una o dos piernas)"),
        ("SCDB", 0.450, "Dos-direcciones Combinado 180º (una o dos piernas)"),
        ("S1",   0.400, "Tirabuzón 360º (una o dos piernas)"),
        ("SC1",  0.950, "Combinado 360º (una o dos piernas)"),
        ("SCD1", 1.000, "Dos-direcciones Combinado 360º (una o dos piernas)"),
        ("S2",   0.900, "Tirabuzón 720º (una o dos piernas)"),
        ("SC2",  1.950, "Combinado 720º (una o dos piernas)"),
        ("SCD2", 2.000, "Dos-direcciones Combinado 720º (una o dos piernas)"),
        ("S3",   1.400, "Tirabuzón 1080º (dos piernas)"),
        ("SC3",  2.950, "Combinado 1080º (dos piernas)"),
        ("SCD3", 3.000, "Dos-direcciones Combinado 1080º (dos piernas)"),
        ("S4",   1.900, "Tirabuzón 1440º (dos piernas)"),
        ("SC4",  3.950, "Combinado 1440º (dos piernas)"),
        ("SCD4", 4.000, "Dos-direcciones Combinado 1440º (dos piernas)"),
        ("S5",   2.400, "Tirabuzón 1800º (dos piernas)"),
        ("SC5",  4.950, "Combinado 1800º (dos piernas)"),
        ("SCD5", 5.000, "Dos-direcciones Combinado 1800º (dos piernas)"),
        ("S6",   2.900, "Tirabuzón 2160º (dos piernas)"),
        ("SC6",  5.750, "Combinado 2160º (dos piernas)"),
        ("SCD6", 5.800, "Dos-direcciones Combinado 2160º (dos piernas)"),
        ("S7",   3.400, "Tirabuzón 2520º (dos piernas)"),
        ("S8",   3.900, "Tirabuzón 2880º (dos piernas)"),
        ("S9",   4.400, "Tirabuzón 3240º (dos piernas)"),
        ("S10",  4.900, "Tirabuzón 3600º (dos piernas)")
    ]
    for cod, val, desc in tirabuzones:
        hibridos_base.append(("Tirabuzones", "", cod, val, desc))

    # GIROS / ROTACIONES
    giros = [
        ("RB",   0.100, "Swirl 180º / Rotación 180º no sostenida o arriba-abajo"),
        ("1RB",  0.150, "Giro/Twirl 180º con una pierna"),
        ("2RB",  0.200, "Giro/Twirl 180º (VP)"),
        ("ROB",  0.250, "PV abrir con 180º de rotación a espagat"),
        ("RCB",  0.250, "Espagat cerrar con 180º de rotación a PV"),
        ("R1",   0.200, "Swirl 360º / Rotación 360º no sostenida o arriba-abajo"),
        ("1R1",  0.350, "Giro/Twirl 360º con una pierna"),
        ("2R1",  0.450, "Giro/Twirl 360º (VP)"),
        ("RD1",  0.500, "Giro dos direcciones 360º (VP)"),
        ("RU1",  0.550, "Giro/Twirl 360º (VP) en desequilibrio"),
        ("RO1",  0.550, "PV abrir con 360º de rotación a espagat"),
        ("RC1",  0.550, "Espagat cerrar con 360º de rotación a PV"),
        ("R2",   0.400, "Swirl 720º"),
        ("1R2",  0.750, "Giro 720º con una pierna"),
        ("2R2",  0.950, "Giro 720º (VP)"),
        ("RD2",  1.050, "Giro dos direcciones 720º (VP)"),
        ("RU2",  1.150, "Giro 720º (VP) en desequilibrio"),
        ("R3",   0.600, "Swirl 1080º"),
        ("1R3",  1.150, "Giro 1080º con una pierna"),
        ("2R3",  1.450, "Giro 1080º (VP)"),
        ("RU3",  1.750, "Giro 1080º (VP) en desequilibrio"),
        ("R4",   0.800, "Swirl 1440º"),
        ("1R4",  1.550, "Giro 1440º con una pierna"),
        ("2R4",  1.950, "Giro 1440º (VP)"),
        ("RD4",  2.150, "Giro dos direcciones 1440º (VP)"),
        ("RU4",  2.350, "Giro 1440º (VP) en desequilibrio"),
        ("1R5",  1.950, "Giro 1800º con una pierna"),
        ("2R5",  2.450, "Giro 1800º (VP)"),
        ("RU5",  2.950, "Giro 1800º (VP) en desequilibrio"),
        ("1R6",  2.350, "Giro 2160º con una pierna"),
        ("2R6",  2.950, "Giro 2160º (VP)"),
        ("RD6",  3.350, "Giro dos direcciones (VP) 2160º"),
        ("RU6",  3.550, "Giro 2160º (VP) en desequilibrio"),
        ("2R7",  3.450, "Giro 2520º (VP)"),
        ("RU7",  4.150, "Giro 2520º (VP) en desequilibrio"),
        ("2R8",  3.950, "Giro 2880º (VP)"),
        ("RU8",  4.750, "Giro 2880º (VP) en desequilibrio"),
        ("2R9",  4.450, "Giro 3240º (VP)"),
        ("RU9",  5.350, "Giro 3240º (VP) en desequilibrio"),
        ("2R10", 4.950, "Giro 3600º (VP)"),
        ("RU10", 5.950, "Giro 3600º (VP) en desequilibrio")
    ]
    for cod, val, desc in giros:
        hibridos_base.append(("Giros", "", cod, val, desc))

    # PESO AÉREO
    peso_aereo = [
        ("AB",  0.050, "Elevar a cualquier posición de una pierna desde posición encogida invertida, mesa o variantes"),
        ("A1a", 0.100, "Elevar a cualquier posición de una pierna desde carpa de frente"),
        ("A1b", 0.100, "Descenso de una pierna"),
        ("A1c", 0.100, "Elevar a PV desde posición encogida invertida, mesa o variantes"),
        ("A1d", 0.100, "Juntar a PV desde Cola de Pez, PV Rodilla doblada o espagat"),
        ("A2a", 0.150, "Descenso Vertical en PV (con o sin movimientos aislados)"),
        ("A2b", 0.150, "Desde carpa de frente a posición de una pierna mientras se rotan 180º"),
        ("A3a", 0.200, "Carpa de frente a PV (elevación de marsopa)"),
        ("A3b", 0.200, "Ascenso vertical con una o dos piernas (con o sin movimientos aislados)"),
        ("A4a", 0.450, "Desde carpa de frente a posición de una pierna mientras se rotan 360º"),
        ("A4b", 0.450, "Carpa de frente a PV mientras se rotan 180º"),
        ("A5",  0.650, "Carpa de frente a PV mientras se rotan 360º"),
        ("A6",  0.800, "3 segundos de movimientos arriba y abajo con una pierna o combinación de una y dos piernas"),
        ("A7",  0.900, "3 segundos de movimientos arriba y abajo con dos piernas"),
        ("A8",  1.150, "Altura sostenida con una pierna o combinación 3s o movimientos aislados pierna estática 3s+"),
        ("A9",  1.450, "Altura sostenida en Posición Vertical durante 3s o más"),
        ("A10", 1.650, "Altura sostenida en Posición Vertical en Desequilibrio 3s o más")
    ]
    for cod, val, desc in peso_aereo:
        hibridos_base.append(("Airbone Weight", "", cod, val, desc))

    # FLEXIBILIDAD
    flexibilidad = [
        ("FB",  0.050, "P. Estirada de espalda a p. arqueada en superficie o arqueada en superficie rodilla doblada"),
        ("F1a", 0.100, "Espagat rápido desde cualquier posición"),
        ("F1b", 0.100, "P- Caballero rápido"),
        ("F1c", 0.100, "Arqueado en superficie Rodilla doblada a Caballero"),
        ("F2a", 0.200, "Salida de paseo de frentes (hasta respirar)"),
        ("F2b", 0.200, "Espagat a carpa de frente (arco de 180º con pierna estirada)"),
        ("F2c", 0.200, "Variantes de espagat en la superficie (demostración de al menos 2 espagats distintos)"),
        ("F3a", 0.300, "Espagat a Espagat por PV (cambiando piernas)"),
        ("F3b", 0.300, "Rotación de Ariana"),
        ("F3c", 0.300, "Combinación de P.Caballero Derecho e Izdo"),
        ("F3d", 0.300, "Cola de Pez a Caballero o Caballero a Cola de Pez pasando por PV Rodilla Doblada"),
        ("F4a", 0.350, "P.Estirada de frente rodilla doblada a p.arqueada en superficie rodilla doblada o espagat"),
        ("F4b", 0.350, "Desde p.arqueada en superficie a Caballero o Espagat con pierna estirada"),
        ("F4c", 0.350, "P.Arqueada en superficie rodilla doblada a PV Rodilla Doblada"),
        ("F4d", 0.350, "P.Arqueada en superficie rodilla doblada a Caballero"),
        ("F4e", 0.350, "Cola de Pez a Caballero o Caballero a Cola de Pez plano horizontal por la superficie"),
        ("F4f", 0.350, "Cola de Pez a Caballero por la PV"),
        ("F5a", 0.400, "Caballero a PV o Caballero a Cola de Pez pasando por PV"),
        ("F5b", 0.400, "P.Arqueada en superficie rodilla doblada a PV"),
        ("F5c", 0.400, "Caballero rotación 180º"),
        ("F6a", 0.450, "Ciclón 180º (desde Arqueado en Sup Rod Doblada Twirl 180º a PV)"),
        ("F6b", 0.450, "Caballero rotación 360º"),
        ("F6c", 0.450, "Juntar de Caballero a PV mientras se rotan 180º"),
        ("F6d", 0.450, "Espagat plano o variantes de espagat plano mantenido en superficie 3s o más"),
        ("F7",  0.500, "P.Arqueada en superficie a PV (con parada clara en PV)"),
        ("F8a", 0.650, "Juntar de Caballero a PV vertical mientras se rotan 360º"),
        ("F8b", 0.650, "Arqueado en superficie rodilla doblada a PV rotando 360º (Nova rotando 360º)"),
        ("F9",  0.750, "P.Arqueada en superficie a PV rotando 180º"),
        ("F10", 1.000, "P.Arqueada en superficie a PV rotando 360º")
    ]
    for cod, val, desc in flexibilidad:
        hibridos_base.append(("Flexibilidad", "", cod, val, desc))

    # CONEXIONES
    conexiones = [
        ("CB",   0.100, "Conexión carpado superficie cono PV"),
        ("CB+",  0.200, "Conexión carpado superficie cono PV (4-10 dep)"),
        ("C1a",  0.200, "Conexión 1 pierna con pierna abajo"),
        ("C1b",  0.200, "Conexión pierna abajo con pierna PV delante"),
        ("C1a+", 0.300, "Conexión 1 pierna con pierna abajo (4-10 dep)"),
        ("C1b+", 0.300, "Conexión pierna abajo con pierna PV delante (4-10 dep)"),
        ("C2a",  0.300, "Conexión cara a cara 1 pierna"),
        ("C2b",  0.300, "Conexión espalda/frente 1 pierna"),
        ("C2c",  0.300, "Conexión C1 con rotación 180º"),
        ("C2a+", 0.400, "Conexión cara a cara 1 pierna (4-10 dep)"),
        ("C2b+", 0.400, "Conexión espalda/frente 1 pierna (4-10 dep)"),
        ("C2c+", 0.400, "Conexión C1 con rotación 180º (4-10 dep)"),
        ("C3",   0.400, "Conexión 1 pierna espaldas/lateral"),
        ("C3+",  0.500, "Conexión 1 pierna espaldas/lateral (4-10 dep)"),
        ("C4",   0.500, "Conexión 2 piernas vertical"),
        ("C4+",  0.600, "Conexión 2 piernas vertical (4-10 dep)"),
        ("C5",   1.000, "Conexión rotación vertical 1 pierna 180º+"),
        ("C5+",  1.100, "Conexión rotación vertical 1 pierna 180º+ (4-10 dep)"),
        ("C6",   1.250, "Conexión rotación vertical 2 piernas 180º+"),
        ("C6+",  1.350, "Conexión rotación vertical 2 piernas 180º+ (4-10 dep)"),
        ("C7",   1.500, "Conexión rotación vertical 2 piernas 360º+"),
        ("C7+",  1.600, "Conexión rotación vertical 2 piernas 360º+ (4-10 dep)")
    ]
    for cod, val, desc in conexiones:
        hibridos_base.append(("Conexiones", "", cod, val, desc))

    orden_select = 200
    for agr, subagr, cod, val, desc in hibridos_base:
        # 1.0
        desc_escaped = desc.replace("'", "''")
        sql.append(f"INSERT INTO dificultad_hibridos (codigo, valor, agrupar, subagrupar, descripcion, orden_select, version, temporada) VALUES ('{cod}', {val:.3f}, '{agr}', '{subagr}', '{desc_escaped}', {orden_select}, '2627', '2026-2027');")
        orden_select += 100
        # 0.5
        val_05 = round(val * 0.5, 3)
        sql.append(f"INSERT INTO dificultad_hibridos (codigo, valor, agrupar, subagrupar, descripcion, orden_select, version, temporada) VALUES ('{cod}*0.5', {val_05:.3f}, '{agr}', '{subagr}', '{desc_escaped} (*0.5)', {orden_select}, '2627', '2026-2027');")
        orden_select += 100
        # 0.3
        val_03 = round(val / 3.0, 3)
        sql.append(f"INSERT INTO dificultad_hibridos (codigo, valor, agrupar, subagrupar, descripcion, orden_select, version, temporada) VALUES ('{cod}*0.3', {val_03:.3f}, '{agr}', '{subagr}', '{desc_escaped} (*0.3)', {orden_select}, '2627', '2026-2027');")
        orden_select += 100

    # BONUS (PC..6PC)
    bonuses = [
        ("PC",  0.200, "1 Cambio de formación"),
        ("2PC", 0.400, "2 Cambios de formación"),
        ("3PC", 0.600, "3 Cambios de formación"),
        ("4PC", 0.800, "4 Cambios de formación"),
        ("5PC", 1.000, "5 Cambios de formación"),
        ("6PC", 1.200, "6 Cambios de formación")
    ]
    for cod, val, desc in bonuses:
        sql.append(f"INSERT INTO dificultad_hibridos (codigo, valor, agrupar, subagrupar, descripcion, orden_select, version, temporada) VALUES ('{cod}', {val:.3f}, 'Bonus', '', '{desc}', 47000, '2627', '2026-2027');")

    # CREATIVO
    sql.append(f"INSERT INTO dificultad_hibridos (codigo, valor, agrupar, subagrupar, descripcion, orden_select, version, temporada) VALUES ('ShoHY', 0.000, 'Creativo', '', 'Híbrido Creativo / Demostración', 70000, '2627', '2026-2027');")
    sql.append("")

    # 5. DIFICULTAD_ACROBACIAS & DIFICULTAD_ACROPAIR
    sql.append("-- --------------------------------------------------------------------")
    sql.append("-- 4. DIFICULTAD_ACROBACIAS & DIFICULTAD_ACROPAIR (2627)")
    sql.append("-- --------------------------------------------------------------------")
    sql.append("""
INSERT INTO dificultad_acrobacias (codigo, valor, descripcion, agrupar, subagrupar, imagen, orden_select, version, temporada)
SELECT codigo, valor, descripcion, agrupar, subagrupar, imagen, orden_select, '2627', '2026-2027'
FROM dificultad_acrobacias
WHERE version = '2425';
""")
    sql.append("""
INSERT INTO dificultad_acropair (codigo, valor, descripcion, agrupar, subagrupar, imagen, version, temporada)
SELECT codigo, valor, descripcion, agrupar, subagrupar, imagen, '2627', '2026-2027'
FROM dificultad_acropair
WHERE version = '2425';
""")

    # Nuevas acrobacias acropair (Addendum)
    nuevas_acropair = [
        ("SL!f2r0.5+r1>", 2.300, "Sustained lift legs up with traveling and 2 consecutive flexibility pushes with clear catch + rot 180 and 360", "Acropair", "Lift"),
        ("SL!f2r1+r1",    2.300, "Sustained lift legs-up with 2 consecutive flexibility pushes with clear catch + rot 360 in each", "Acropair", "Lift"),
        ("W!s0.5p",       1.550, "Throw legs-up with 180o somersault in Pike", "Acropair", "Throw"),
        ("JBs1t1",        2.500, "Jump head up with 1 somersault + 1 twist", "Acropair", "Jump"),
        ("JBss1t1",       2.900, "Jump head up – 1 straight somersault + 1 twist", "Acropair", "Jump"),
        ("Jdt1",          1.900, "Jump Dive Full Twist", "Acropair", "Jump"),
        ("Js1.5B",        2.300, "Jump head-up with 1.5 backward somersault", "Acropair", "Jump"),
        ("Js1B+pt",       2.000, "Jump backwards in pike + tuck 360 somersault", "Acropair", "Jump")
    ]
    for cod, val, desc, agr, subagr in nuevas_acropair:
        desc_escaped = desc.replace("'", "''")
        sql.append(f"INSERT INTO dificultad_acropair (codigo, valor, descripcion, agrupar, subagrupar, imagen, version, temporada) VALUES ('{cod}', {val:.3f}, '{desc_escaped}', '{agr}', '{subagr}', 0, '2627', '2026-2027');")
        sql.append(f"INSERT INTO dificultad_acrobacias (codigo, valor, descripcion, agrupar, subagrupar, imagen, orden_select, version, temporada) VALUES ('{cod}', {val:.3f}, '{desc_escaped}', 'ACROPAIR', '{agr}', 0, 9999, '2627', '2026-2027');")

    # Nuevas acrobacias equipo (Addendum)
    nuevas_acro_equipo = [
        ("r1.5L+", 1.000, "Group B – Rotation of construction (base) 540º or more for Lift", "ACRO-B", "Rotación Base"),
        ("4L",     1.450, "Group P Construction – 4 level platform construction", "ACRO-P", "Plataforma"),
        ("Weasel", 1.500, "Group P Construction – Platform Turtle position blind capture", "ACRO-P", "Plataforma"),
        ("Mov!",   0.200, "Group B – Bonus ending action: Lift to handstand position", "ACRO-B", "Ending Bonus"),
        ("RetF",   1.000, "Group A Bonus: Return on Feet Construction with Featured Swimmer handstand", "ACRO-A", "Bonus"),
        ("Spiral", 0.300, "Group B Bonus - ending action: Lift to handstand on heads", "ACRO-B", "Ending Bonus"),
        ("Bloom",  0.900, "Group B Bonus - non-stop transition to Pos 1 while stack head-up rising", "ACRO-B", "Bonus"),
        ("Cs2",    0.550, "Group C – Plane and degree of rotation: 2 somersaults", "ACRO-C", "Rotación"),
        ("Cs2t0.5",0.800, "Group C – 2 somersaults + 1/2 twist", "ACRO-C", "Rotación"),
        ("Lotus",  0.300, "Group P ending action", "ACRO-P", "Ending Bonus"),
        ("Ps1t1o", 0.450, "Platform 360 somersault + 1 twist open to straight", "ACRO-P", "Salida"),
        ("Ps1.5!", 0.300, "Platform head-down 540 somersault", "ACRO-P", "Salida"),
        ("2F1.5",  0.450, "Group C rotation 540º flown over in Thr^2F", "ACRO-C", "Rotación")
    ]
    for cod, val, desc, agr, subagr in nuevas_acro_equipo:
        desc_escaped = desc.replace("'", "''")
        sql.append(f"INSERT INTO dificultad_acrobacias (codigo, valor, descripcion, agrupar, subagrupar, imagen, orden_select, version, temporada) VALUES ('{cod}', {val:.3f}, '{desc_escaped}', '{agr}', '{subagr}', 0, 9999, '2627', '2026-2027');")

    output_path = "/var/www/html/sincrm/database/migracion_2627.sql"
    with open(output_path, "w", encoding="utf-8") as f:
        f.write("\n".join(sql))
    print(f"Generado exitosamente {output_path} con {len(sql)} líneas SQL.")

if __name__ == "__main__":
    generate_sql()
