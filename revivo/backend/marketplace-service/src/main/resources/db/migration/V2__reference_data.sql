-- Données de référence : catégories (arbre à 2 niveaux) et marques

INSERT INTO categories (id, name, slug, parent_id) VALUES
 (1, 'Femmes', 'femmes', NULL),
 (2, 'Hommes', 'hommes', NULL),
 (3, 'Enfants', 'enfants', NULL),
 (4, 'Maison', 'maison', NULL),
 (5, 'Divertissement', 'divertissement', NULL),
 (10, 'Robes', 'femmes-robes', 1),
 (11, 'Hauts & t-shirts', 'femmes-hauts', 1),
 (12, 'Manteaux & vestes', 'femmes-manteaux', 1),
 (13, 'Jeans', 'femmes-jeans', 1),
 (14, 'Chaussures', 'femmes-chaussures', 1),
 (15, 'Sacs', 'femmes-sacs', 1),
 (20, 'T-shirts', 'hommes-tshirts', 2),
 (21, 'Sweats & pulls', 'hommes-sweats', 2),
 (22, 'Manteaux & vestes', 'hommes-manteaux', 2),
 (23, 'Jeans', 'hommes-jeans', 2),
 (24, 'Chaussures', 'hommes-chaussures', 2),
 (25, 'Montres & accessoires', 'hommes-accessoires', 2),
 (30, 'Fille', 'enfants-fille', 3),
 (31, 'Garçon', 'enfants-garcon', 3),
 (32, 'Jouets', 'enfants-jouets', 3),
 (33, 'Puériculture', 'enfants-puericulture', 3),
 (40, 'Décoration', 'maison-deco', 4),
 (41, 'Linge de maison', 'maison-linge', 4),
 (42, 'Cuisine', 'maison-cuisine', 4),
 (50, 'Livres', 'divertissement-livres', 5),
 (51, 'Jeux vidéo', 'divertissement-jeux-video', 5),
 (52, 'Musique & films', 'divertissement-musique', 5);
SELECT setval('categories_id_seq', 100);

INSERT INTO brands (name) VALUES
 ('Adidas'), ('Nike'), ('Zara'), ('H&M'), ('Levi''s'), ('Mango'), ('Uniqlo'),
 ('The North Face'), ('Patagonia'), ('Converse'), ('Vans'), ('New Balance'),
 ('Ralph Lauren'), ('Lacoste'), ('Petit Bateau'), ('Kiabi'), ('IKEA'),
 ('Sézane'), ('Sandro'), ('Maje'), ('Carhartt'), ('Dr. Martens'), ('Autre');
