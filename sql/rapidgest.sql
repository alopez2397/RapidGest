CREATE DATABASE rapidgest CHARACTER SET utf8;
USE rapidgest;

CREATE TABLE familias (
  idfamilias INT AUTO_INCREMENT PRIMARY KEY,
  familia VARCHAR(45)
);

CREATE TABLE articulos (
  idarticulos INT AUTO_INCREMENT PRIMARY KEY,
  articulo VARCHAR(60),
  pvp DOUBLE(10,2),
  idfamilias INT,
  FOREIGN KEY (idfamilias) REFERENCES familias(idfamilias)
);

CREATE TABLE pedidos (
  idpedidos INT AUTO_INCREMENT PRIMARY KEY,
  fecha DATE,
  hora TIME,
  total DOUBLE(10,2),
  empleado VARCHAR(45),
  cobrado ENUM('S','N') DEFAULT 'N',
  servido ENUM('S','N') DEFAULT 'N'
);

CREATE TABLE lineaspedido (
  idlineasPedido INT AUTO_INCREMENT PRIMARY KEY,
  idpedidos INT,
  idarticulos INT,
  pvp DOUBLE(10,2),
  FOREIGN KEY (idpedidos) REFERENCES pedidos(idpedidos) ON DELETE CASCADE,
  FOREIGN KEY (idarticulos) REFERENCES articulos(idarticulos)
);
