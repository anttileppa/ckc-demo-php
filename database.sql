create table Property (
  name varchar(255) not null, 
  id bigint not null auto_increment, 
  primary key (id)
);

create table Document (
  data longtext not null,
  revisionNumber bigint not null,
  id bigint not null auto_increment, 
  primary key (id)
);

create table Revision (
  document_id bigint not null,
  patch longtext not null,
  number bigint not null,
  id bigint not null auto_increment, 
  primary key (id)
);

create table RevisionProperty (
  revision_id bigint not null,
  property_id bigint not null,
  value varchar(1024) not null,
  id bigint not null auto_increment, 
  primary key (id)  
);

create table DocumentProperty (
  document_id bigint not null,
  property_id bigint not null,
  value varchar(1024) not null,
  id bigint not null auto_increment, 
  primary key (id)  
);

alter table Revision add index FK_REVISION_DOCUMENT_ID (document_id), add constraint FK_REVISION_DOCUMENT_ID foreign key (document_id) references Document (id);
alter table RevisionProperty add index FK_REVISION_PROPERTY_REVISION_ID (revision_id), add constraint FK_REVISION_PROPERTY_REVISION_ID foreign key (revision_id) references Revision (id);
alter table RevisionProperty add index FK_REVISION_PROPERTY_PROPERTY_ID (property_id), add constraint FK_REVISION_PROPERTY_PROPERTY_ID foreign key (property_id) references Property (id);
alter table DocumentProperty add index FK_DOCUMENT_PROPERTY_DOCUMENT_ID (document_id), add constraint FK_DOCUMENT_PROPERTY_DOCUMENT_ID foreign key (document_id) references Document (id);
alter table DocumentProperty add index FK_DOCUMENT_PROPERTY_PROPERTY_ID (property_id), add constraint FK_DOCUMENT_PROPERTY_PROPERTY_ID foreign key (property_id) references Property (id);
