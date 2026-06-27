# Boundary Detection

Boundary detection combines static and runtime data into a graph and clusters nodes into candidate service boundaries.

## Algorithms

### Table Affinity Clusterer (default)

Groups tables that are frequently touched together in runtime traces, then attaches routes, controllers, models, events, and jobs to each cluster.

1. Build table graph from co-occurrence and model relationships
2. Start each table as its own cluster
3. Merge clusters sorted by edge weight descending
4. Stop when cohesion would decrease or external coupling would increase

### Greedy Modularity Clusterer

A simple modularity-optimization clustering algorithm for weighted undirected graphs.

## Scoring

- **Cohesion**: How strongly nodes inside a boundary relate (0–1)
- **Coupling**: How much a boundary depends on external nodes (0–1)
- **Risk**: Weighted combination of coupling, shared writes, transaction complexity, raw SQL uncertainty, and missing test signals

## Candidate Naming

Names are derived from route prefixes, namespaces, table names, and controller names. Users can override with `--name`.
