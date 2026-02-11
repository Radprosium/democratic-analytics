// Democratic Analytics - Graph schema (Neo4j)
// Run with: bin/console app:neo4j:init-schema

// Core nodes
CREATE CONSTRAINT person_id IF NOT EXISTS
FOR (p:Person)
REQUIRE p.personId IS UNIQUE;

CREATE CONSTRAINT organization_id IF NOT EXISTS
FOR (o:Organization)
REQUIRE o.orgId IS UNIQUE;

CREATE CONSTRAINT position_id IF NOT EXISTS
FOR (pos:Position)
REQUIRE pos.positionId IS UNIQUE;

CREATE CONSTRAINT event_id IF NOT EXISTS
FOR (e:Event)
REQUIRE e.eventId IS UNIQUE;

// Search/indexes
CREATE INDEX person_name IF NOT EXISTS
FOR (p:Person)
ON (p.name);

CREATE INDEX organization_name IF NOT EXISTS
FOR (o:Organization)
ON (o.name);

CREATE INDEX position_title IF NOT EXISTS
FOR (pos:Position)
ON (pos.title);

CREATE INDEX event_title IF NOT EXISTS
FOR (e:Event)
ON (e.title);

// Suggested relationship properties (not enforced by Neo4j):
// - source (string)
// - confidence (float 0..1)
// - start_date (date)
// - end_date (date)
// - weight (float)